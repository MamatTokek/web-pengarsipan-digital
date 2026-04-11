<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    /**
     * Menampilkan daftar aktivitas dengan tabel 3 kolom.
     */
    public function index()
    {
        // Mengambil data aktivitas beserta data user terkait
        // Diurutkan dari yang terbaru (descending)
        $activities = Activity::with('user')
            ->latest()
            ->paginate(10); // 10 data per halaman

        return view('kades.activities.index', compact('activities'));
    }
}