<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'type' => 'required|in:archive,letter', // Pastikan tipe juga tervalidasi
        ]);

        // 2. Simpan Data ke Database
        $category = Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'type' => $request->type,
        ]);

        // 3. LOGIKA RESPONS (AJAX vs NORMAL)

        // Jika permintaan datang melalui AJAX (Fetch API dari Modal)
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Kategori berhasil ditambahkan!',
                'category' => $category // Mengirim data kategori baru agar bisa diproses JavaScript
            ]);
        }

        // Jika permintaan datang dari halaman Normal Redirect (Fallback)
        // Cek apakah user datang dari halaman Edit Arsip
        if ($request->has('from_edit')) {
            return redirect()->route('archives.edit', $request->from_edit)
                             ->with('success', 'Kategori baru berhasil ditambahkan!');
        }

        // Default redirect jika bukan AJAX
        return redirect()->route('archives.create')->with('success', 'Kategori baru berhasil ditambahkan!');
    }
}