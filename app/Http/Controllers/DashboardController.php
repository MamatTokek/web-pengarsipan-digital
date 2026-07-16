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

        $docType = $request->input('doc_type'); // 'letter' atau 'archive'
        $categoryId = $request->input('category');
        $status = $request->input('status');
        $month = $request->input('month');
        $year = $request->input('year', date('Y')); // Default tahun berjalan

        // 1. STATISTIK (Bawaan)
        $totalLetters = Letter::count(); 
        $totalArchives = Archive::count();
        $totalDocuments = $totalLetters + $totalArchives;

        // MODIFIKASI: Hitung kategori utama saja (Bawaan)
        $totalCategories = Category::where(function($query) {
            $query->whereIn('name', ['Surat Masuk', 'Surat Keluar']) // Kategori Utama Surat
                ->orWhere('type', 'archive');                      // Kategori Arsip
        })
        ->whereNull('kode_surat') // Memastikan kode jenis tidak ikut terhitung
        ->count(); 
        
        // 2. QUERY DOKUMEN TERBARU (Bawaan)
        $lettersQuery = DB::table('letters')
            ->select('id', 'uuid', 'letter_number', 'name', 'original_file_name', 'category_id', 'updated_at as uploaded_at',
                    'need_action', 'action_status', 'admin_note',
                    DB::raw("'letter' as type"));
            
        $archivesQuery = DB::table('archives')
            ->select('id', 'uuid', 'letter_number', 'name', 'original_file_name', 'category_id', 'updated_at as uploaded_at',
                    DB::raw('NULL as need_action'), DB::raw('NULL as action_status'), DB::raw('NULL as admin_note'),
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

        // =========================================================================
        // KODE BARU: Tambahkan Filter ini agar Tabel ikut tersaring dengan Grafik
        // =========================================================================
        if ($docType) {
            $latestDocumentsQuery->where('type', $docType);
        }
        if ($categoryId) {
            $latestDocumentsQuery->where('category_id', $categoryId);
        }
        if ($status && (!$docType || $docType === 'letter')) {
            if ($status === 'pending') {
                $latestDocumentsQuery->where('need_action', true)->where('action_status', 'pending');
            } elseif ($status === 'completed') {
                $latestDocumentsQuery->where('need_action', true)->where('action_status', 'completed');
            } elseif ($status === 'no_action') {
                $latestDocumentsQuery->where('need_action', false);
            }
        }
        if ($month) {
            $latestDocumentsQuery->whereMonth('uploaded_at', $month);
        }
        if ($year) {
            $latestDocumentsQuery->whereYear('uploaded_at', $year);
        }

        // AMBIL DATA: Urutkan berdasarkan uploaded_at TERBARU dan ID TERBESAR
        $latestDocuments = $latestDocumentsQuery
                            ->orderBy('uploaded_at', 'desc')
                            ->orderBy('id', 'desc')
                            ->limit(10)
                            ->get();
        
        // 3. LOAD RELASI CATEGORY (Optimasi manual bawaan)
        $categoryIds = $latestDocuments->pluck('category_id')->filter()->unique();
        $categoriesMap = Category::whereIn('id', $categoryIds)->get()->keyBy('id');

        // KODE BARU: Tarik data surat balasan dari model Letter berdasarkan id dokumen
        $letterIds = $latestDocuments->where('type', 'letter')->pluck('id')->unique();
        $replyLettersMap = Letter::whereIn('reply_to_id', $letterIds)
            ->get()
            ->keyBy('reply_to_id'); // Kelompokkan berdasarkan ID surat induk yang dibalas

        // Pasangkan objek kategori ke masing-masing dokumen
        $latestDocuments = $latestDocuments->map(function ($document) use ($categoriesMap, $replyLettersMap) {
            $document->category = $categoriesMap->get($document->category_id);

            // Pasangkan objek data balasan jika tipe dokumen adalah surat dan memiliki balasan
            $document->reply_letter = $document->type === 'letter' ? $replyLettersMap->get($document->id) : null;
            return $document;
        });

        // =========================================================================
        // 4. LOGIKA GRAFIK DOKUMEN DAN FILTER BERTINGKAT (TAMBAHAN BARU)
        // =========================================================================
        // Sub-query Grafik Surat
        $lettersChartQuery = DB::table('letters')
            ->select(
                DB::raw("MONTH(updated_at) as month_num"),
                DB::raw("COUNT(*) as total")
            )
            ->whereYear('updated_at', $year)
            ->when($docType === 'archive', function ($query) {
                return $query->whereRaw('1 = 0');
            })
            ->when($categoryId, function ($query) use ($categoryId) {
                return $query->where('category_id', $categoryId);
            })
            ->when($status, function ($query) use ($status) {
                if ($status === 'pending') {
                    return $query->where('need_action', true)->where('action_status', 'pending');
                } elseif ($status === 'completed') {
                    return $query->where('need_action', true)->where('action_status', 'completed');
                } elseif ($status === 'no_action') {
                    return $query->where('need_action', false);
                }
            })
            ->when($month, function ($query) use ($month) {
                return $query->whereMonth('updated_at', $month);
            })
            ->groupBy('month_num');

        // Sub-query Grafik Arsip
        $archivesChartQuery = DB::table('archives')
            ->select(
                DB::raw("MONTH(updated_at) as month_num"),
                DB::raw("COUNT(*) as total")
            )
            ->whereYear('updated_at', $year)
            ->when($docType === 'letter', function ($query) {
                return $query->whereRaw('1 = 0');
            })
            ->when($categoryId, function ($query) use ($categoryId) {
                return $query->where('category_id', $categoryId);
            })
            ->when($month, function ($query) use ($month) {
                return $query->whereMonth('updated_at', $month);
            })
            ->groupBy('month_num');

        // Gabungkan query grafik
        $unionChartQuery = $lettersChartQuery->unionAll($archivesChartQuery);
        $rawChartData = DB::table(DB::raw("({$unionChartQuery->toSql()}) as combined"))
            ->mergeBindings($unionChartQuery)
            ->select('month_num', DB::raw('SUM(total) as total'))
            ->groupBy('month_num')
            ->get()
            ->keyBy('month_num');

        // Susun array hasil untuk Chart.js (Januari - Desember)
        $chartData = [];
        for ($m = 1; $m <= 12; $m++) {
            $chartData[] = $rawChartData->has($m) ? (int) $rawChartData->get($m)->total : 0;
        }

        // Ambil opsi master kategori untuk dropdown filter kondisional
        $letterCategories = Category::where('type', 'letter')->whereNull('kode_surat')->orderBy('name', 'asc')->get();
        $archiveCategories = Category::where('type', 'archive')->whereNull('kode_surat')->orderBy('name', 'asc')->get();
        
        // Data yang dikirim ke view
        $data = [
            'totalDocuments' => $totalDocuments,
            'totalArchives'  => $totalArchives,
            'totalLetters' => $totalLetters,
            'latestDocuments' => $latestDocuments,
            'search' => $search,
            
            // Passing variabel baru untuk kebutuhan Grafik & Filter
            'chartData' => json_encode($chartData),
            'letterCategories' => $letterCategories,
            'archiveCategories' => $archiveCategories,
            'currentDocType' => $docType,
            'currentCategory' => $categoryId,
            'currentStatus' => $status,
            'currentMonth' => $month,
            'currentYear' => $year,
            'years' => range(date('Y'), 2022)
        ];

        // 5. LOGIKA PENGALIHAN VIEW BERDASARKAN ROLE
        if (Auth::user()->role === 'super_role') {
            return view('super_admin.dashboard', $data);
        }

        return view('dashboard', $data);
    }
}