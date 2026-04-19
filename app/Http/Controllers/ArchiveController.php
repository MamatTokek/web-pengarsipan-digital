<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Archive;
use App\Models\Activity; 
use App\Models\Letter; // Diperlukan untuk query pengecekan nomor urut
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

// Import Library untuk Stamping
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use setasign\Fpdi\Fpdi;

class ArchiveController extends Controller
{
    /**
     * Menampilkan daftar arsip dengan pencarian dan filter.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $categoryFilter = $request->input('category');
        $monthFilter = $request->input('month');
        $yearFilter = $request->input('year');
        
        $archivesQuery = Archive::with('category')->orderBy('updated_at', 'desc'); 

        if ($search) {
            $searchLower = strtolower($search); 
            $archivesQuery->where(function ($query) use ($searchLower) {
                $query->whereRaw('LOWER(name) LIKE ?', ["%{$searchLower}%"])
                      ->orWhereRaw('LOWER(letter_number) LIKE ?', ["%{$searchLower}%"])
                      ->orWhereRaw('LOWER(description) LIKE ?', ["%{$searchLower}%"])
                      ->orWhereRaw('LOWER(original_file_name) LIKE ?', ["%{$searchLower}%"])
                      ->orWhereHas('category', function ($q) use ($searchLower) {
                          $q->whereRaw('LOWER(name) LIKE ?', ["%{$searchLower}%"]);
                      });
            });
        }

        if ($categoryFilter) {
            $archivesQuery->where('category_id', $categoryFilter);
        }

        if ($monthFilter) {
            $archivesQuery->whereMonth('uploaded_at', $monthFilter);
        }

        if ($yearFilter) {
            $archivesQuery->whereYear('uploaded_at', $yearFilter);
        }

        $archives = $archivesQuery->paginate(10)->withQueryString(); 
        
        // Hanya menampilkan kategori utama arsip (bukan kode jenis)
        $categories = Category::where('type', 'archive')
            ->whereNull('kode_surat')
            ->get();
        
        return view('archives.index', compact('archives', 'categories', 'search', 'categoryFilter', 'monthFilter', 'yearFilter'));
    }

    public function create()
    {
        // Kategori utama untuk arsip
        $categories = Category::where('type', 'archive')
            ->whereNull('kode_surat')
            ->get(); 
            
        // Ambil semua kategori dengan kode untuk perakit (sama dengan di Kelola Surat)
        $autoCategories = Category::whereNotNull('kode_surat')->get();

        return view('archives.create', compact('categories', 'autoCategories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'letter_number' => 'nullable|string|max:255|unique:archives,letter_number', 
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'category_id' => 'required|exists:categories,id', 
            'file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ], [
            // Pesan Error Kustom
            'file.mimes' => 'Format file tidak didukung! Gunakan PDF, Word (DOC/DOCX), atau Gambar (JPG, JPEG, PNG).',
            'file.max' => 'Ukuran file maksimal adalah 10MB.',
        ]);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        $uuid = (string) Str::uuid(); 
        $originalName = $file->getClientOriginalName();
        $fileName = time() . '_' . $originalName;
        
        $filePath = $file->storeAs('archives', $fileName, 'public'); 
        
        $this->stampQrCode($filePath, $uuid, $extension);

        $archive = Archive::create([
            'uuid' => $uuid,
            'letter_number' => $request->letter_number,
            'name' => $request->name,
            'description' => $request->description,
            'original_file_name' => $originalName,
            'category_id' => $request->category_id,
            'file_path' => $filePath,
            'uploaded_at' => now(),
        ]);

        Activity::create([
            'user_id' => Auth::id(),
            'description' => 'baru saja menambahkan arsip baru',
            'subject_name' => $archive->name,
            'type' => 'Arsip'
        ]);

        return redirect()->route('archives.index')->with('success', 'Arsip berhasil ditambahkan!');
    }

    private function stampQrCode($filePath, $uuid, $extension)
    {
        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode(route('public.verify.archive', $uuid));
        $tempQrPath = storage_path('app/public/temp_qr_archive_' . $uuid . '.png');
        
        try {
            file_put_contents($tempQrPath, file_get_contents($qrUrl));
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
    
    public function show(Archive $archive)
    {
        if (!Storage::disk('public')->exists($archive->file_path)) {
            abort(404);
        }
        $path = Storage::disk('public')->path($archive->file_path);
        return response()->file($path);
    }

    public function download(Archive $archive)
    {
        if (Storage::disk('public')->exists($archive->file_path)) {
            return Storage::disk('public')->download($archive->file_path, $archive->original_file_name);
        }
        return redirect()->back()->with('error', 'File tidak ditemukan.');
    }

    public function edit(Archive $archive)
    {
        $categories = Category::where('type', 'archive')
            ->whereNull('kode_surat')
            ->get();
        
        $autoCategories = Category::whereNotNull('kode_surat')->get();

        return view('archives.edit', compact('archive', 'categories', 'autoCategories'));
    }

    public function update(Request $request, Archive $archive)
    {
        $request->validate([
            'letter_number' => 'nullable|string|max:255|unique:archives,letter_number,' . $archive->id,
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'category_id' => 'required|exists:categories,id',
            'file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240', 
        ], [
            // Pesan Error Kustom
            'file.mimes' => 'Format file tidak didukung! Gunakan PDF, Word (DOC/DOCX), atau Gambar (JPG, JPEG, PNG).',
            'file.max' => 'Ukuran file maksimal adalah 10MB.',
        ]);
        
        $data = $request->only('name', 'description', 'category_id', 'letter_number');

        if ($request->hasFile('file')) {
            if (Storage::disk('public')->exists($archive->file_path)) {
                Storage::disk('public')->delete($archive->file_path);
            }

            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $originalName = $file->getClientOriginalName();
            $fileName = time() . '_' . $originalName;
            $filePath = $file->storeAs('archives', $fileName, 'public');

            $data['file_path'] = $filePath;
            $data['original_file_name'] = $originalName;

            $this->stampQrCode($filePath, $archive->uuid, $extension);
        }

        $archive->update($data);

        Activity::create([
            'user_id' => Auth::id(),
            'description' => 'baru saja memperbarui data arsip',
            'subject_name' => $archive->name,
            'type' => 'Arsip'
        ]);

        return redirect()->route('archives.index')->with('success', 'Arsip berhasil diperbarui!');
    }

    public function destroy(Archive $archive)
    {
        $archiveName = $archive->name;
        if (Storage::disk('public')->exists($archive->file_path)) {
            Storage::disk('public')->delete($archive->file_path);
        }

        Activity::create([
            'user_id' => Auth::id(),
            'description' => 'telah menghapus arsip',
            'subject_name' => $archiveName,
            'type' => 'Arsip'
        ]);

        $archive->delete();
        return redirect()->route('archives.index')->with('success', 'Arsip berhasil dihapus!');
    }

    public function publicVerify($uuid)
    {
        $doc = Archive::with('category')->where('uuid', $uuid)->firstOrFail();
        return view('public.verify', [
            'doc' => $doc,
            'type' => 'Arsip'
        ]);
    }
}