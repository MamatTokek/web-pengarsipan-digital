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
        // TANGKAP INPUT SEARCH & FILTER DARI REQUEST
        $search = $request->input('search');
        $docType = $request->input('doc_type'); // 'letter' atau 'archive'
        $categoryId = $request->input('category');
        $status = $request->input('status');
        $month = $request->input('month');
        $year = $request->input('year', date('Y')); // Default tahun berjalan

        // 1. STATISTIK (COUNT)
        $totalLetters = Letter::count(); 
        $totalArchives = Archive::count();
        $totalDocuments = $totalLetters + $totalArchives;

        // Hitung surat yang butuh tindakan dan berstatus pending (Bawaan Kades)
        $pendingActionsCount = Letter::where('need_action', true)
                                ->where('action_status', 'pending')
                                ->count();
        
        // 2. QUERY DOKUMEN TERBARU (UNION)
        
        // A. LETTERS (Ditambahkan need_action, action_status, admin_note)
        $latestLetters = DB::table('letters')
            ->select('id', 'uuid', 'letter_number', 'name', 'original_file_name', 'category_id', 'updated_at as uploaded_at', 
                    'need_action', 'action_status', 'admin_note',
                    DB::raw("'letter' as type")); 
            
        // B. ARCHIVES (Ditambahkan NULL sebagai penyeimbang)
        $latestArchives = DB::table('archives')
            ->select('id', 'uuid', 'letter_number', 'name', 'original_file_name', 'category_id', 'updated_at as uploaded_at', 
                    DB::raw('NULL as need_action'), DB::raw('NULL as action_status'), DB::raw('NULL as admin_note'),
                    DB::raw("'archive' as type")); 

        // C. Gabungkan keduanya menggunakan UNION
        $allLatestDocumentsQuery = $latestLetters->union($latestArchives);
        
        // Gunakan subquery pembungkus agar pencarian global dan filter bekerja serentak
        $latestDocumentsQuery = DB::table(DB::raw("({$allLatestDocumentsQuery->toSql()}) as combined"))
                                    ->mergeBindings($allLatestDocumentsQuery);

        // Logika Pencarian Global Bawaan
        if ($search) {
            $searchLower = strtolower($search);
            $latestDocumentsQuery->where(function($q) use ($searchLower) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$searchLower}%"])
                ->orWhereRaw('LOWER(letter_number) LIKE ?', ["%{$searchLower}%"])
                ->orWhereRaw('LOWER(original_file_name) LIKE ?', ["%{$searchLower}%"]);
            });
        }

        // Penerapan Filter Aktif Pada Tabel Dokumen Terbaru
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

        // Ambil Hasil Akhir untuk Data Tabel Kades
        $latestDocuments = $latestDocumentsQuery
                            ->orderBy('uploaded_at', 'desc')
                            ->orderBy('id', 'desc')
                            ->limit(10)
                            ->get();
        
        // D. Load relasi Category & Surat Balasan secara manual
        $categoryIds = $latestDocuments->pluck('category_id')->filter()->unique();
        $categoriesMap = Category::whereIn('id', $categoryIds)->get()->keyBy('id');

        $letterIds = $latestDocuments->where('type', 'letter')->pluck('id')->unique();
        $replyLettersMap = Letter::whereIn('reply_to_id', $letterIds)->get()->keyBy('reply_to_id');

        $latestDocuments = $latestDocuments->map(function ($document) use ($categoriesMap, $replyLettersMap) {
            $document->category = $categoriesMap->get($document->category_id);
            $document->reply_letter = $document->type === 'letter' ? $replyLettersMap->get($document->id) : null;
            return $document;
        });

        // =========================================================================
        // 3. LOGIKA GRAFIK DOKUMEN BERTINGKAT UNTUK KADES
        // =========================================================================
        // Sub-query Grafik Surat
        $lettersChartQuery = DB::table('letters')
            ->select(DB::raw("MONTH(updated_at) as month_num"), DB::raw("COUNT(*) as total"))
            ->whereYear('updated_at', $year)
            ->when($docType === 'archive', function ($query) { return $query->whereRaw('1 = 0'); })
            ->when($categoryId, function ($query) use ($categoryId) { return $query->where('category_id', $categoryId); })
            ->when($status, function ($query) use ($status) {
                if ($status === 'pending') return $query->where('need_action', true)->where('action_status', 'pending');
                if ($status === 'completed') return $query->where('need_action', true)->where('action_status', 'completed');
                if ($status === 'no_action') return $query->where('need_action', false);
            })
            ->when($month, function ($query) use ($month) { return $query->whereMonth('updated_at', $month); })
            ->groupBy('month_num');

        // Sub-query Grafik Arsip
        $archivesChartQuery = DB::table('archives')
            ->select(DB::raw("MONTH(updated_at) as month_num"), DB::raw("COUNT(*) as total"))
            ->whereYear('updated_at', $year)
            ->when($docType === 'letter', function ($query) { return $query->whereRaw('1 = 0'); })
            ->when($categoryId, function ($query) use ($categoryId) { return $query->where('category_id', $categoryId); })
            ->when($month, function ($query) use ($month) { return $query->whereMonth('updated_at', $month); })
            ->groupBy('month_num');

        // Gabungkan query grafik
        $unionChartQuery = $lettersChartQuery->unionAll($archivesChartQuery);
        $rawChartData = DB::table(DB::raw("({$unionChartQuery->toSql()}) as combined"))
            ->mergeBindings($unionChartQuery)
            ->select('month_num', DB::raw('SUM(total) as total'))
            ->groupBy('month_num')
            ->get()
            ->keyBy('month_num');

        // Susun array untuk Chart.js (Januari - Desember)
        $chartData = [];
        for ($m = 1; $m <= 12; $m++) {
            $chartData[] = $rawChartData->has($m) ? (int) $rawChartData->get($m)->total : 0;
        }

        // Ambil opsi master kategori untuk dropdown filter kondisional Kades
        $letterCategories = Category::where('type', 'letter')->whereNull('kode_surat')->orderBy('name', 'asc')->get();
        $archiveCategories = Category::where('type', 'archive')->whereNull('kode_surat')->orderBy('name', 'asc')->get();

        // 4. KIRIM SEMUA VARIABEL KE VIEW KADES
        return view('kades.dashboard', [
            'totalDocuments' => $totalDocuments,
            'totalArchives'  => $totalArchives,
            'totalLetters'   => $totalLetters,
            'latestDocuments' => $latestDocuments, 
            'pendingActionsCount' => $pendingActionsCount,

            // Variabel Tambahan Grafik & Filter Bertingkat
            'chartData' => json_encode($chartData),
            'letterCategories' => $letterCategories,
            'archiveCategories' => $archiveCategories,
            'currentDocType' => $docType,
            'currentCategory' => $categoryId,
            'currentStatus' => $status,
            'currentMonth' => $month,
            'currentYear' => $year,
            'years' => range(date('Y'), 2022)
        ]);
    }
}