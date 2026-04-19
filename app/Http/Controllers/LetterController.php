<?php

namespace App\Http\Controllers;

use App\Models\Category; 
use App\Models\Letter;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

// Import Library untuk Stamping
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use setasign\Fpdi\Fpdi;

class LetterController extends Controller
{
    /**
     * Menampilkan daftar surat dengan urutan terbaru di atas (Berdasarkan ID).
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $categoryFilter = $request->input('category');
        $statusFilter = $request->input('status');
        $monthFilter = $request->input('month');
        $yearFilter = $request->input('year');

        // Menggunakan orderBy id DESC agar data terbaru selalu di atas secara konsisten
        $lettersQuery = Letter::with(['category', 'replyLetter'])
            ->orderBy('updated_at', 'desc')
            ->orderBy('id', 'desc'); // Cadangan jika updated_at identik

        // Filter Pencarian - MODIFIKASI: Menambahkan pencarian berdasarkan letter_number
        if ($search) {
            $lettersQuery->where(function ($query) use ($search) {
                $searchLower = strtolower($search); 
                $query->whereRaw('LOWER(name) LIKE ?', ["%{$searchLower}%"])
                    ->orWhereRaw('LOWER(letter_number) LIKE ?', ["%{$searchLower}%"])
                    ->orWhereRaw('LOWER(original_file_name) LIKE ?', ["%{$searchLower}%"]);
            });
        }

        // Filter Kategori
        if ($categoryFilter) {
            $lettersQuery->where('category_id', $categoryFilter);
        }

        // Filter Status Tindakan
        if ($statusFilter) {
            if ($statusFilter === 'pending') {
                $lettersQuery->where('need_action', true)->where('action_status', 'pending');
            } elseif ($statusFilter === 'completed') {
                $lettersQuery->where('need_action', true)->where('action_status', 'completed');
            } elseif ($statusFilter === 'no_action') {
                $lettersQuery->where('need_action', false);
            }
        }

        // Filter Bulan dan Tahun
        if ($request->filled('month')) {
            $lettersQuery->whereMonth('uploaded_at', $monthFilter);
        }
        if ($request->filled('year')) {
            $lettersQuery->whereYear('uploaded_at', $yearFilter);
        }

        $letters = $lettersQuery->paginate(10)->withQueryString(); 
        $categories = Category::where('type', 'letter')
            ->whereIn('name', ['Surat Masuk', 'Surat Keluar'])
            ->get(); 
        
        $data = [
            'letters' => $letters,
            'search' => $search,
            'categories' => $categories,
            'categoryFilter' => $categoryFilter,
            'statusFilter' => $statusFilter,
            'monthFilter' => $monthFilter,
            'yearFilter' => $yearFilter,
            'years' => range(date('Y'), 2022)
        ];

        return view($request->routeIs('kades.*') ? 'kades.letters.index' : 'letters.index', $data);
    }

    public function store(Request $request)
    {
        // 1. VALIDASI (Diselaraskan dengan Frontend)
        $request->validate([
            'letter_number' => 'required|string|max:255|unique:letters,letter_number',
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            // UBAH: Menyesuaikan mimes ke dokumen dan menaikkan limit ukuran ke 10MB
            'file' => 'required|file|mimes:pdf|max:10240', 
        ], [
            // Pesan Error Kustom agar lebih informatif
            'file.mimes' => 'Format file tidak didukung! Gunakan PDF',
            'file.max' => 'Ukuran file maksimal adalah 10MB.',
        ]);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        $originalName = $file->getClientOriginalName();
        $uuid = (string) Str::uuid(); 
        
        $fileName = time() . '_' . $originalName;
        
        // Simpan ke disk 'public'
        $filePath = $file->storeAs('letters', $fileName, 'public');

        // JALANKAN PROSES STEMPEL QR CODE
        $this->stampQrCode($filePath, $uuid, $extension);

        $needAction = $request->has('need_action');

        // SIMPAN KE DATABASE
        $letter = Letter::create([
            'uuid' => $uuid,
            'letter_number' => $request->letter_number,
            'name' => $request->name,
            'original_file_name' => $originalName,
            'category_id' => $request->category_id,
            'file_path' => $filePath,
            'uploaded_at' => now(),
            'need_action' => $needAction,
            'action_status' => $needAction ? 'pending' : null,
            'admin_note' => $needAction ? $request->admin_note : null,
        ]);

        // CATAT AKTIVITAS
        Activity::create([
            'user_id' => Auth::id(),
            'description' => 'baru saja menambahkan surat baru',
            'subject_name' => $letter->name,
            'type' => 'Surat'
        ]);

        return redirect()->route('letters.index')->with('success', 'Surat berhasil ditambahkan');
    }

    private function stampQrCode($filePath, $uuid, $extension)
    {
        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode(route('public.verify.letter', $uuid));
        $tempQrPath = storage_path('app/public/temp_qr_' . $uuid . '.png');
        
        try {
            $qrImage = file_get_contents($qrUrl);
            file_put_contents($tempQrPath, $qrImage);
            
            $fullPath = storage_path('app/public/' . $filePath);

            if (strtolower($extension) == 'pdf') {
                $pdf = new Fpdi();
                $pageCount = $pdf->setSourceFile($fullPath);
                
                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                    $templateId = $pdf->importPage($pageNo);
                    $size = $pdf->getTemplateSize($templateId);
                    $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                    $pdf->useTemplate($templateId);

                    if ($pageNo == $pageCount) {
                        $pdf->Image($tempQrPath, $size['width'] - 35, $size['height'] - 35, 25, 25);
                    }
                }
                $pdf->Output($fullPath, 'F');

            } elseif (in_array(strtolower($extension), ['jpg', 'jpeg', 'png'])) {
                $manager = new ImageManager(new Driver());
                $image = $manager->read($fullPath);
                $overlay = $manager->read($tempQrPath);
                
                $image->place($overlay, 'bottom-right', 20, 20);
                $image->save($fullPath);
            }

            if (File::exists($tempQrPath)) { File::delete($tempQrPath); }

        } catch (\Exception $e) {
            if (File::exists($tempQrPath)) { File::delete($tempQrPath); }
        }
    }

    public function download(Letter $letter)
    {
        if (Storage::disk('public')->exists($letter->file_path)) {
            return Storage::disk('public')->download($letter->file_path, $letter->original_file_name);
        }
        return redirect()->back()->with('error', 'File tidak ditemukan di server.');
    }

    public function show(Letter $letter)
    {
        if (!Storage::disk('public')->exists($letter->file_path)) {
            abort(404);
        }
        $path = Storage::disk('public')->path($letter->file_path);
        return response()->file($path);
    }

    public function update(Request $request, Letter $letter)
    {
        // 1. Validasi Input
        $request->validate([
            'letter_number' => 'required|string|max:255|unique:letters,letter_number,' . $letter->id,
            'name' => 'required|string|max:255',
            'file' => 'nullable|file|mimes:pdf|max:10240', // Maksimal 10MB
            'category_id' => 'required|exists:categories,id',
        ], [
            // Pesan Error Kustom agar lebih informatif
            'file.mimes' => 'Format file tidak didukung! Gunakan PDF',
            'file.max' => 'Ukuran file maksimal adalah 10MB.',
        ]);

        $letter->name = $request->name;
        $letter->letter_number = $request->letter_number;
        $letter->category_id = $request->category_id;

        // 2. Logika Penggantian File
        if ($request->hasFile('file')) {
            // A. Hapus file surat asli yang lama dari storage
            if ($letter->file_path && Storage::disk('public')->exists($letter->file_path)) { 
                Storage::disk('public')->delete($letter->file_path); 
            }

            /**
             * B. LOGIKA RESET SURAT MASUK (Bukan ID 2):
             * Jika admin mengganti dokumen referensi surat masuk yang sudah selesai,
             * hapus balasan lamanya dan kembalikan status ke 'pending'.
             */
            if ($letter->category_id != 2 && $letter->action_status === 'completed') {
                $reply = \App\Models\Letter::where('reply_to_id', $letter->id)->first();
                if ($reply) {
                    // Hapus file fisik balasannya
                    if ($reply->file_path && Storage::disk('public')->exists($reply->file_path)) {
                        Storage::disk('public')->delete($reply->file_path);
                    }
                    $reply->delete(); // Hapus baris data balasan dari database
                }
                
                // Set status kembali ke pending
                $letter->action_status = 'pending';
            }

            /**
             * C. LOGIKA RESET SURAT KELUAR (Kategori ID 2):
             * Jika admin mengganti draf surat keluar yang sudah selesai/sah,
             * kembalikan status ke 'pending' agar Kades melakukan stamping ulang.
             */
            if ($letter->category_id == 2 && $letter->action_status === 'completed') {
                $letter->action_status = 'pending';
            }

            // D. Simpan File Baru
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $letter->original_file_name = $file->getClientOriginalName();
            $fileName = time() . '_' . Str::slug($letter->name) . '.' . $extension;
            
            $letter->file_path = $file->storeAs('letters', $fileName, 'public');

            // Lakukan stamping QR ulang pada file baru tersebut
            $this->stampQrCode($letter->file_path, $letter->uuid, $extension);
        }

        // 3. Update Status Tindakan
        if ($request->has('need_action')) {
            $letter->need_action = true;
            $letter->admin_note = $request->admin_note;
            
            // Jika sebelumnya tidak butuh tindakan, set status awal menjadi pending
            if (!$letter->getOriginal('need_action')) { 
                $letter->action_status = 'pending'; 
            }
        } else {
            $letter->need_action = false;
            $letter->admin_note = null;
            $letter->action_status = null;
        }

        $letter->save();

        // 4. Catat Aktivitas
        Activity::create([
            'user_id' => Auth::id(),
            'description' => 'telah memperbarui data dan file surat asli',
            'subject_name' => $letter->name,
            'type' => 'Surat'
        ]);

        return redirect()->route('letters.index')->with('success', 'Surat berhasil diperbarui');
    }

    public function destroy(Letter $letter)
    {
        // Hanya Super Role yang boleh menghapus permanen
        if (auth()->user()->role !== 'super_role') {
            return redirect()->back()->with('error', 'Anda tidak memiliki hak akses untuk menghapus data. Silakan hubungi Super Role via menu Pesan.');
        }

        $letter->delete();
        return redirect()->back()->with('success', 'Data berhasil dihapus oleh Super Role.');
    }

    public function showDetail(Letter $letter)
    {
        $letter->load(['category', 'replyLetter']);
        return view('letters.show_detail', compact('letter'));
    }

    public function create()
    {
        // Ambil hanya kategori Surat Masuk dan Surat Keluar untuk dropdown utama
        // Asumsi: Nama kategorinya adalah 'Surat Masuk' dan 'Surat Keluar'
        $categories = Category::where('type', 'letter')
            ->whereIn('name', ['Surat Masuk', 'Surat Keluar'])
            ->get(); 

        // Ambil semua kategori yang memiliki kode_surat untuk mesin perakit
        $autoCategories = Category::whereNotNull('kode_surat')->get();

        return view('letters.create', compact('categories', 'autoCategories'));
    }

    public function edit(Letter $letter)
    {
        // Ambil kategori utama (Surat Masuk & Keluar)
        $categories = Category::where('type', 'letter')
            ->whereIn('name', ['Surat Masuk', 'Surat Keluar'])
            ->get();

        // Ambil semua kategori dengan kode untuk perakit
        $autoCategories = Category::whereNotNull('kode_surat')->get();

        return view('letters.edit', compact('letter', 'categories', 'autoCategories'));
    }

    public function publicVerify($uuid)
    {
        $doc = Letter::with('category')->where('uuid', $uuid)->firstOrFail();
        return view('public.verify', ['doc' => $doc, 'type' => 'Surat']);
    }

    public function getNextNumber($kode)
    {
        // Mengambil tahun saat ini
        $currentYear = date('Y'); //

        // Mencari surat terakhir yang:
        // 1. Memiliki kode yang sama di awal
        // 2. Memiliki tahun yang sama di akhir nomor surat
        $lastLetter = \App\Models\Letter::where('letter_number', 'LIKE', $kode . '/%')
            ->where('letter_number', 'LIKE', '%/' . $currentYear) // Tambahan filter tahun
            ->orderBy('id', 'desc')
            ->first();

        if ($lastLetter) {
            // Memecah string nomor surat
            $parts = explode('/', $lastLetter->letter_number);
            // Mengambil bagian nomor urut dan ditambah 1
            $nextNumber = isset($parts[1]) ? (int)$parts[1] + 1 : 1;
        } else {
            // Jika belum ada surat dengan kode tersebut DI TAHUN INI, mulai dari 1
            $nextNumber = 1; //
        }

        return response()->json([
            'next_number' => str_pad($nextNumber, 3, '0', STR_PAD_LEFT)
        ]);
    }
}