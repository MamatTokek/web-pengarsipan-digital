<?php

namespace App\Http\Controllers;

use App\Models\Archive;
use App\Models\Category;
use App\Models\Letter;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;

// Library untuk Stamping QR
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use setasign\Fpdi\Fpdi;

class DocumentController extends Controller
{
    /**
     * Menampilkan daftar semua Surat dan Arsip (digabungkan).
     * Mendukung tampilan untuk Kepala Desa dan Super Role secara dinamis.
     */
    public function index(Request $request)
    {
        // MODIFIKASI: Hanya mengambil kategori untuk surat dan arsip
        $categories = Category::whereNull('kode_surat')
            ->orderBy('name', 'asc')
            ->get();

        $search = $request->input('search'); // Biarkan teks asli (case-insensitive diatur di fungsi TF-IDF)
        $categoryId = $request->input('category');
        $status = $request->input('status');
        $month = $request->input('month');
        $year = $request->input('year'); 
        $type = $request->input('type'); 
        $perPage = 10;

        // --- QUERY LETTERS (SURAT) ---
        $lettersQuery = DB::table('letters')
            ->select('id', 'uuid', 'letter_number', 'name', 'original_file_name', 'category_id', 'updated_at as uploaded_at', 
                    'need_action', 'action_status', 'admin_note',
                    DB::raw("'Surat' as type_label"),
                    DB::raw("'letter' as type"))
            ->when($year, function ($query) use ($year) {
                return $query->whereYear('uploaded_at', $year);
            })
            ->when($type == 'Arsip', function ($query) {
                return $query->whereRaw('1 = 0');
            })
            // CATATAN: Logika WHERE LIKE bawaan dihapus karena digantikan oleh kalkulasi TF-IDF di bawah
            ->when($categoryId, function ($query) use ($categoryId) {
                return $query->where('category_id', $categoryId);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('action_status', $status);
            })
            ->when($month, function ($query) use ($month) {
                return $query->whereMonth('uploaded_at', $month);
            });

        // --- QUERY ARCHIVES (ARSIP) ---
        $archivesQuery = DB::table('archives')
            ->select('id', 'uuid', 'letter_number', 'name', 'original_file_name', 'category_id', 'updated_at as uploaded_at', 
                    DB::raw('NULL as need_action'), DB::raw('NULL as action_status'), DB::raw('NULL as admin_note'),
                    DB::raw("'Arsip' as type_label"),
                    DB::raw("'archive' as type"))
            ->when($year, function ($query) use ($year) {
                return $query->whereYear('uploaded_at', $year);
            })
            ->when($type == 'Surat', function ($query) {
                return $query->whereRaw('1 = 0');
            })
            ->when($status, function ($query) {
                return $query->whereRaw('1 = 0');
            })
            // CATATAN: Logika WHERE LIKE bawaan dihapus karena digantikan oleh kalkulasi TF-IDF di bawah
            ->when($categoryId, function ($query) use ($categoryId) {
                return $query->where('category_id', $categoryId);
            })
            ->when($month, function ($query) use ($month) {
                return $query->whereMonth('uploaded_at', $month);
            });

        $unionQuery = $lettersQuery->union($archivesQuery);
        $allDocumentsRaw = DB::table(DB::raw("({$unionQuery->toSql()}) as combined"))
            ->mergeBindings($unionQuery)
            ->get();
        
        // Pemetaan Relasi Kategori Dokumen
        $categoryIds = $allDocumentsRaw->pluck('category_id')->unique()->filter();
        $categoriesMap = Category::whereIn('id', $categoryIds)->get()->keyBy('id');

        $allDocuments = $allDocumentsRaw->map(function ($document) use ($categoriesMap) {
            $document->category = $categoriesMap->get($document->category_id);
            $document->route_name_prefix = $document->type_label === 'Surat' ? 'letters' : 'archives';
            return $document;
        });

        // --- PROSES INTI TF-IDF + COSINE SIMILARITY ---
        if ($search) {
            $corpus = [];
            foreach ($allDocuments as $document) {
                $categoryName = $document->category ? $document->category->name : '';
                // Korpus teks gabungan: Nama + Nomor + File Asli + Nama Kategori
                $corpus[$document->type . '_' . $document->id] = $document->name . ' ' . $document->letter_number . ' ' . $document->original_file_name . ' ' . $categoryName;
            }

            // Kalkulasi skor matematika kosinus
            $similarities = $this->calculateCosineSimilarity($search, $corpus);

            $filteredDocuments = collect();
            foreach ($similarities as $key => $score) {
                if ($score > 0.05) {
                    list($docType, $docId) = explode('_', $key);
                    $foundDoc = $allDocuments->first(function ($item) use ($docType, $docId) {
                        return $item->type == $docType && $item->id == $docId;
                    });
                    if ($foundDoc) {
                        $foundDoc->similarity_score = $score;
                        $filteredDocuments->push($foundDoc);
                    }
                }
            }
            $sortedCollection = $filteredDocuments;
        } else {
            // Jika kotak pencarian kosong, urutkan berdasarkan update terbaru secara standar
            $sortedCollection = $allDocuments->sort(function ($a, $b) {
                if ($a->uploaded_at === $b->uploaded_at) {
                    return $b->id <=> $a->id; // Jika detiknya sama, ID terbesar naik ke atas
                }
                return $b->uploaded_at <=> $a->uploaded_at;
            })->values();
        }

        // --- PAGINASI MANUAL ---
        $page = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
        // Gunakan slice() untuk memotong koleksi data yang sudah diurutkan skor kemiripannya
        $items = $sortedCollection->slice(($page - 1) * $perPage, $perPage)->all();
        
        $documents = new \Illuminate\Pagination\LengthAwarePaginator($items, $sortedCollection->count(), $perPage, $page, [
            'path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
            'query' => $request->query(),
        ]);
        
        // PENGALIHAN VIEW BERDASARKAN ROLE
        if (Auth::user()->role === 'super_role') {
            return view('super_admin.documents_index', [
                'documents' => $documents,
                'categories' => $categories,
                'search' => $request->input('search'),
            ]);
        }

        return view('kades.documents.index', [
            'documents' => $documents,
            'categories' => $categories,
            'search' => $request->input('search'),
            'years' => range(date('Y'), 2022), 
        ]);
    }

    public function showAction(Letter $letter)
    {
        $reply = Letter::where('reply_to_id', $letter->id)->first();
        $letter->load('category');
        return view('kades.documents.show_action', compact('letter', 'reply'));
    }

    public function getNextNumber($kode)
    {
        // Mengambil tahun saat ini
        $currentYear = date('Y'); 

        // PERBAIKAN 1: Tambahkan ->where('category_id', 2) agar nomor urut Surat Masuk manual tidak mencemari Surat Keluar
        // PERBAIKAN 2: Gunakan pola '_%/%' agar mencari data yang polanya diawali No Urut baru diikuti Kode Jenis
        $lastLetter = \App\Models\Letter::where('category_id', 2)
            ->where('letter_number', 'LIKE', '_%/' . $kode . '/%')
            ->where('letter_number', 'LIKE', '%/' . $currentYear)
            ->orderBy('letter_number', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastLetter) {
            // Memecah string nomor surat (Contoh format baru: 005/SR/PEM/VI/2026)
            $parts = explode('/', $lastLetter->letter_number);
            
            // PERBAIKAN 3: Ambil indeks 0 karena nomor urut sekarang resmi pindah ke bagian paling awal string
            $nextNumber = isset($parts[0]) ? (int)$parts[0] + 1 : 1;
        } else {
            // Jika belum ada Surat Keluar berkode tersebut di tahun ini, mulai dari 1
            $nextNumber = 1; 
        }

        return response()->json([
            'next_number' => str_pad($nextNumber, 3, '0', STR_PAD_LEFT)
        ]);
    }

    public function reply(Letter $letter)
    {
        if (!$letter->need_action) {
            return redirect()->back()->with('error', 'Surat ini tidak membutuhkan tindakan.');
        }
        $source = request('source');
        $categories = Category::whereNotNull('kode_surat')->orderBy('id', 'asc')->get();
        return view('kades.documents.reply', compact('letter', 'source', 'categories'));
    }

    /**
     * Menyimpan balasan dengan Stamping QR Code.
     */
    public function storeReply(Request $request, Letter $letter)
    {
        // 1. Validasi Dasar
        $rules = [
            'file' => 'required|file|mimes:pdf|max:10240',
        ];

        // Jika Surat Masuk, Kades wajib isi nomor surat baru untuk balasannya
        if ($letter->category_id != 2) { // Asumsi ID 2 adalah Surat Keluar
            $rules['letter_number'] = [
                'required',
                'string',
                'max:255',
                'not_in:--/--/--/--/--', // Mencegah draf kosong lolos
                'unique:letters,letter_number'
            ];
            $rules['name'] = 'required|string|max:255';
        }

        $request->validate($rules, [
            'letter_number.not_in' => 'Bapak Kepala Desa, mohon rakit Nomor Surat terlebih dahulu pada blok Perakit Nomor Surat.',
            'letter_number.unique' => 'Nomor surat ini sudah terdaftar di sistem, silakan gunakan nomor urut lain.',
            'file.mimes' => 'Bapak Kepala Desa, mohon gunakan format PDF untuk balasan surat.',
            'file.max'   => 'Ukuran file balasan maksimal adalah 10MB.',
        ]);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        $uuid = (string) Str::uuid();

        // 2. LOGIKA GANDA BERDASARKAN KATEGORI
        if ($letter->category_id == 2) { 
            /** * OPSi A: SURAT KELUAR (Logika Timpa File / Pengesahan)
             * Menimpa file draft dengan file yang sudah ditandatangani.
             */
            if ($letter->file_path && Storage::disk('public')->exists($letter->file_path)) {
                Storage::disk('public')->delete($letter->file_path);
            }

            $fileName = time() . '_signed_' . Str::slug($letter->name) . '.' . $extension;
            $filePath = $file->storeAs('letters', $fileName, 'public');
            $this->stampQrCode($filePath, $letter->uuid, $extension);

            $letter->update([
                'file_path' => $filePath,
                'original_file_name' => $file->getClientOriginalName(),
                'action_status' => 'completed',
                'uploaded_at' => now(),
            ]);
            $letter->touch();

        } else {
            /** * OPSi B: SURAT MASUK (Logika Balas) */
            $fileName = time() . '_balasan_' . Str::slug($request->name) . '.' . $extension;
            $filePath = $file->storeAs('letters', $fileName, 'public');
            $this->stampQrCode($filePath, $uuid, $extension);
        
            // PASTIkan baris ini membuat data dengan reply_to_id yang benar
            Letter::create([
                'uuid' => $uuid,
                'letter_number' => $request->letter_number,
                'name' => $request->name,
                'original_file_name' => $file->getClientOriginalName(),
                'category_id' => 2,
                'file_path' => $filePath,
                'uploaded_at' => now(),
                'reply_to_id' => $letter->id, // INI HARUS TERISI ID SURAT ASAL
                'need_action' => false,
                'action_status' => null,
            ]);
        
            $letter->update(['action_status' => 'completed']);
            $letter->touch();
        }

        // 3. Log Aktivitas
        Activity::create([
            'user_id' => Auth::id(),
            'description' => ($letter->category_id == 2) ? 'telah mengesahkan surat' : 'telah membalas surat masuk',
            'subject_name' => $letter->name,
            'type' => 'Surat'
        ]);

        return redirect()->route('kades.documents.index')->with('success', 'Tindakan berhasil diproses.');
    }

    public function editReply(Letter $letter)
    {
        $source = request('source');

        // LOGIKA HYBRID
        if ($letter->category_id == 2) {
            // Jika Surat Keluar: Yang diedit adalah surat itu sendiri (Proses Re-Upload Sah)
            $reply = $letter; 
        } else {
            // Jika Surat Masuk: Cari surat balasannya
            $reply = Letter::where('reply_to_id', $letter->id)->firstOrFail();
        }

        $categories = Category::where('type', 'letter')
            ->whereNotNull('kode_surat')
            ->where('kode_surat', '!=', '')
            ->orderBy('kode_surat', 'asc')
            ->get();

        return view('kades.documents.edit_reply', compact('letter', 'reply', 'source', 'categories'));
    }

    /**
     * Update balasan dengan Stamping QR Code ulang jika file diganti.
     */
    public function updateReply(Request $request, Letter $letter)
    {
        // 1. Tentukan target dokumen yang akan diupdate fungsinya
        // Jika Kategori ID 2 (Surat Keluar), maka targetnya adalah diri sendiri (timpa file)
        // Jika bukan, maka targetnya adalah baris balasan yang merujuk ke surat ini
        if ($letter->category_id == 2) {
            $targetDoc = $letter; 
        } else {
            $targetDoc = Letter::where('reply_to_id', $letter->id)->firstOrFail();
        }

        // 2. Atur validasi secara dinamis
        $rules = [
            'file' => 'nullable|file|mimes:pdf|max:10240',
        ];

        // Jika Surat Masuk (bukan ID 2), validasi nama dan nomor surat balasan
        if ($letter->category_id != 2) {
            $rules['letter_number'] = [
                'required',
                'string',
                'max:255',
                'not_in:--/--/--/--/--', // Mengunci draf kosong agar tidak masuk DB
                'unique:letters,letter_number,' . $targetDoc->id
            ];
            $rules['name'] = 'required|string|max:255';
        }

        $request->validate($rules, [
            'letter_number.not_in' => 'Bapak Kepala Desa, mohon rakit Nomor Surat terlebih dahulu pada blok Perakit Nomor Surat.',
            'letter_number.unique' => 'Nomor surat ini sudah terdaftar di sistem, silakan gunakan nomor urut lain.',
            'file.mimes' => 'Format revisi harus berupa PDF!',
            'file.max'   => 'Ukuran file revisi maksimal adalah 10MB.',
        ]);

        // 3. Siapkan data untuk update
        $data = [];
        if ($letter->category_id != 2) {
            $data['name'] = $request->name;
            $data['letter_number'] = $request->letter_number;
        }

        // 4. Logika penanganan file (jika ada file baru diunggah)
        if ($request->hasFile('file')) {
            // Hapus file lama dari storage
            if ($targetDoc->file_path && Storage::disk('public')->exists($targetDoc->file_path)) {
                Storage::disk('public')->delete($targetDoc->file_path);
            }

            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $originalName = $file->getClientOriginalName();
            
            // Nama file disesuaikan dengan konteks (revisi)
            $fileName = time() . '_revisi_' . Str::slug($targetDoc->name) . '.' . $extension;
            $filePath = $file->storeAs('letters', $fileName, 'public');

            $data['original_file_name'] = $originalName;
            $data['file_path'] = $filePath;

            // Proses Stamping QR Code menggunakan UUID dokumen target
            $this->stampQrCode($filePath, $targetDoc->uuid, $extension);
        }

        // 5. Eksekusi Update ke Database
        $targetDoc->update($data);
        
        // Pastikan urutan tetap di atas dengan memperbarui updated_at
        $targetDoc->touch();

        // 6. Catat Aktivitas
        Activity::create([
            'user_id' => Auth::id(),
            'description' => $letter->category_id == 2 ? 'telah memperbarui pengesahan surat' : 'telah memperbarui balasan surat',
            'subject_name' => $letter->name,
            'type' => 'Surat'
        ]);

        // 7. Redirect berdasarkan sumber halaman
        if ($request->input('source') === 'detail') {
            return redirect()->route('kades.documents.show_action', $letter->id)->with('success', 'Perubahan berhasil disimpan.');
        }

        return redirect()->route('kades.documents.index')->with('success', 'Perubahan berhasil disimpan.');
    }

    /**
     * Logika Privat Penempelan QR Code
     */
    private function stampQrCode($filePath, $uuid, $extension)
    {
        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode(route('public.verify.letter', $uuid));
        $tempQrPath = storage_path('app/public/temp_qr_reply_' . $uuid . '.png');
        
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

    public function destroy($id, $type)
    {
        if (Auth::user()->role !== 'super_role') {
            abort(403);
        }

        if ($type === 'letter') {
            $document = Letter::findOrFail($id);
            $label = 'Surat';

            // PERBAIKAN: Gunakan reply_to_id sesuai yang ada di Model Letter Anda
            if ($document->reply_to_id) {
                $originalLetter = Letter::find($document->reply_to_id);
                if ($originalLetter) {
                    // Paksa update ke database
                    $originalLetter->action_status = 'pending';
                    $originalLetter->need_action = true;
                    $originalLetter->save(); // Menggunakan save() lebih aman untuk memastikan data tersimpan
                }
            }
        } else {
            $document = Archive::findOrFail($id);
            $label = 'Arsip';
        }

        $documentName = $document->name;

        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        \App\Models\Activity::create([
            'user_id' => Auth::id(),
            'description' => 'telah menghapus dokumen',
            'subject_name' => $documentName,
            'type' => $label
        ]);

        $document->delete();

        // Gunakan redirect ke index kades agar data ter-refresh sempurna
        return redirect()->route('kades.documents.index')->with('success', 'Dokumen dihapus berhasil dihapus');
    }

    private function calculateCosineSimilarity($query, $corpus)
    {
        // 1. Bersihkan string (Kecilkan huruf dan hapus tanda baca khusus)
        $cleanQuery = preg_replace('/[^a-zA-Z0-9\s]/', '', strtolower($query));
        $queryTerms = array_filter(explode(' ', $cleanQuery));

        $documents = [];
        $allTerms = $queryTerms;

        foreach ($corpus as $id => $text) {
            $cleanText = preg_replace('/[^a-zA-Z0-9\s]/', '', strtolower($text));
            $terms = array_filter(explode(' ', $cleanText));
            $documents[$id] = $terms;
            $allTerms = array_merge($allTerms, $terms);
        }
        
        // Kumpulan kosakata unik dari semua dokumen + query
        $allTerms = array_unique($allTerms); 

        // 2. Hitung Document Frequency (DF) untuk setiap kata
        $df = []; 
        foreach ($allTerms as $term) {
            $df[$term] = 0;
            foreach ($documents as $doc) {
                if (in_array($term, $doc)) {
                    $df[$term]++;
                }
            }
        }

        // 3. Hitung Vektor TF-IDF untuk semua Dokumen di Database
        $matrix = [];
        $totalDocs = count($documents);
        foreach ($documents as $id => $doc) {
            $tf = array_count_values($doc);
            foreach ($allTerms as $term) {
                $termTf = isset($tf[$term]) ? $tf[$term] : 0;
                $termIdf = isset($df[$term]) && $df[$term] > 0 ? log(($totalDocs + 1) / ($df[$term] + 1)) + 1 : 0;
                $matrix[$id][$term] = $termTf * $termIdf;
            }
        }

        // 4. Hitung Vektor TF-IDF untuk kata kunci Pencarian (Query)
        $queryVector = [];
        $queryTf = array_count_values($queryTerms);
        foreach ($allTerms as $term) {
            $termTf = isset($queryTf[$term]) ? $queryTf[$term] : 0;
            $termIdf = isset($df[$term]) && $df[$term] > 0 ? log(($totalDocs + 1) / ($df[$term] + 1)) + 1 : 0;
            $queryVector[$term] = $termTf * $termIdf;
        }

        // 5. HITUNG COSINE SIMILARITY ANTARA QUERY DAN DOKUMEN
        $scores = [];
        $queryMagnitude = 0;
        foreach ($queryVector as $weight) {
            $queryMagnitude += $weight * $weight;
        }
        $queryMagnitude = sqrt($queryMagnitude);

        foreach ($matrix as $id => $docVector) {
            $dotProduct = 0;
            $docMagnitude = 0;
            foreach ($allTerms as $term) {
                $dotProduct += $queryVector[$term] * $docVector[$term];
                $docMagnitude += $docVector[$term] * $docVector[$term];
            }
            $docMagnitude = sqrt($docMagnitude);

            if ($queryMagnitude * $docMagnitude == 0) {
                $scores[$id] = 0;
            } else {
                $scores[$id] = $dotProduct / ($queryMagnitude * $docMagnitude);
            }
        }

        // Urutkan skor kemiripan teks dari yang terbesar ke terkecil
        arsort($scores); 
        return $scores;
    }
}