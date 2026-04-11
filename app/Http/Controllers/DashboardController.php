<?php

namespace App\Http\Controllers;

use App\Models\Archive;
use App\Models\Category;
use App\Models\Letter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Menampilkan Dashboard untuk Admin dan Super Role.
     * View akan diarahkan secara dinamis berdasarkan role user.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        // 1. STATISTIK
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
        
        // 2. QUERY DOKUMEN TERBARU
        $lettersQuery = DB::table('letters')
            ->select('id', 'uuid', 'letter_number', 'name', 'original_file_name', 'category_id', 'uploaded_at', 
                     DB::raw("'letter' as type"));
            
        $archivesQuery = DB::table('archives')
            ->select('id', 'uuid', 'letter_number', 'name', 'original_file_name', 'category_id', 'uploaded_at', 
                     DB::raw("'archive' as type"));

        // Gabungkan menggunakan UNION
        $combinedQuery = $lettersQuery->union($archivesQuery);

        // Bungkus dalam wrapper agar pencarian mencakup semua kolom
        $latestDocumentsQuery = DB::table(DB::raw("({$combinedQuery->toSql()}) as combined"))
            ->mergeBindings($combinedQuery);

        // Logika Pencarian Global
        if ($search) {
            $searchLower = strtolower($search);
            $latestDocumentsQuery->where(function($q) use ($searchLower) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$searchLower}%"])
                  ->orWhereRaw('LOWER(letter_number) LIKE ?', ["%{$searchLower}%"])
                  ->orWhereRaw('LOWER(original_file_name) LIKE ?', ["%{$searchLower}%"]);
            });
        }

        // AMBIL DATA: Urutkan berdasarkan uploaded_at TERBARU dan ID TERBESAR
        $latestDocuments = $latestDocumentsQuery
                            ->orderBy('uploaded_at', 'desc')
                            ->orderBy('id', 'desc')
                            ->limit(10)
                            ->get();
        
        // 3. LOAD RELASI CATEGORY (Optimasi manual)
        $categoryIds = $latestDocuments->pluck('category_id')->filter()->unique();
        $categoriesMap = Category::whereIn('id', $categoryIds)->get()->keyBy('id');

        // Pasangkan objek kategori ke masing-masing dokumen
        $latestDocuments = $latestDocuments->map(function ($document) use ($categoriesMap) {
            $document->category = $categoriesMap->get($document->category_id);
            return $document;
        });
        
        // Data yang dikirim ke view
        $data = [
            'totalDocuments' => $totalDocuments,
            'totalCategories' => $totalCategories,
            'totalLetters' => $totalLetters,
            'latestDocuments' => $latestDocuments,
            'search' => $search,
        ];

        // 4. LOGIKA PENGALIHAN VIEW BERDASARKAN ROLE
        if (Auth::user()->role === 'super_role') {
            return view('super_admin.dashboard', $data);
        }

        return view('dashboard', $data);
    }
}