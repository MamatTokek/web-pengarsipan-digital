<?php

namespace App\Http\Controllers;

use App\Models\Archive;
use App\Models\Category;
use App\Models\Letter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; 

class KadesController extends Controller
{
    public function index(Request $request)
    {
        // 1. STATISTIK (COUNT)
        $totalLetters = Letter::count(); 
        $totalArchives = Archive::count();
        $totalDocuments = $totalLetters + $totalArchives;

        // MODIFIKASI: Hitung kategori utama saja.
        // Mengecualikan kategori yang memiliki 'kode_surat' (Kode Jenis Surat)
        $totalCategories = Category::where(function($query) {
            $query->whereIn('name', ['Surat Masuk', 'Surat Keluar']) // Kategori Utama Surat
                  ->orWhere('type', 'archive');                      // Kategori Arsip
        })
        ->whereNull('kode_surat') // Memastikan kode jenis tidak ikut terhitung
        ->count();

        // Hitung surat yang butuh tindakan dan berstatus pending
        $pendingActionsCount = Letter::where('need_action', true)
                                ->where('action_status', 'pending')
                                ->count();
        
        // 2. QUERY DOKUMEN TERBARU (UNION)
        
        // A. LETTERS
        // Menambahkan 'uuid' dan 'letter_number' ke dalam select
        $latestLetters = DB::table('letters')
            ->select('id', 'uuid', 'letter_number', 'name', 'original_file_name', 'category_id', 'uploaded_at', 
                     DB::raw("'letter' as type")); 
            
        // B. ARCHIVES
        // Menambahkan 'uuid' dan 'letter_number' (Urutan harus sama dengan Letters)
        $latestArchives = DB::table('archives')
            ->select('id', 'uuid', 'letter_number', 'name', 'original_file_name', 'category_id', 'uploaded_at', 
                     DB::raw("'archive' as type")); 

        // C. Gabungkan keduanya, urutkan, dan batasi menjadi 10
        $allLatestDocumentsQuery = $latestLetters->union($latestArchives);
        
        // Gunakan subquery agar OrderBy dan Limit bekerja pada hasil gabungan secara keseluruhan
        $latestDocuments = DB::table(DB::raw("({$allLatestDocumentsQuery->toSql()}) as combined"))
                            ->mergeBindings($allLatestDocumentsQuery)
                            ->orderBy('uploaded_at', 'desc')
                            ->limit(10)
                            ->get();
        
        // D. Load relasi Category secara efisien (Eager Loading manual)
        $categoryIds = $latestDocuments->pluck('category_id')->filter()->unique();
        $categoriesMap = Category::whereIn('id', $categoryIds)->get()->keyBy('id');

        $latestDocuments = $latestDocuments->map(function ($document) use ($categoriesMap) {
            $document->category = $categoriesMap->get($document->category_id);
            return $document;
        });

        // 3. KIRIM KE VIEW
        return view('kades.dashboard', [
            'totalDocuments' => $totalDocuments,
            'totalCategories' => $totalCategories,
            'totalLetters' => $totalLetters,
            'latestDocuments' => $latestDocuments, 
            'pendingActionsCount' => $pendingActionsCount,
        ]);
    }
}