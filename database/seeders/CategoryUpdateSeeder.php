<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class CategoryUpdateSeeder extends Seeder
{
    public function run(): void
    {
        // Daftar 15 kategori baru sesuai gambar referensi Anda
        $data = [
            ['name' => 'Surat Keputusan (SK)', 'kode' => '01'],
            ['name' => 'Surat Undangan (SU)', 'kode' => '02'],
            ['name' => 'Surat Permohonan (SPm)', 'kode' => '03'],
            ['name' => 'Surat Pemberitahuan (SPb)', 'kode' => '04'],
            ['name' => 'Surat Peminjaman (SPp)', 'kode' => '05'],
            ['name' => 'Surat Pernyataan (SPn)', 'kode' => '06'],
            ['name' => 'Surat Mandat (SM)', 'kode' => '07'],
            ['name' => 'Surat Tugas (ST)', 'kode' => '08'],
            ['name' => 'Surat Keterangan (SKet)', 'kode' => '09'],
            ['name' => 'Surat Rekomendasi (SR)', 'kode' => '10'],
            ['name' => 'Surat Balasan (SB)', 'kode' => '11'],
            ['name' => 'Surat Perintah Perjalanan Dinas (SPPD)', 'kode' => '12'],
            ['name' => 'Sertifikat (SRT)', 'kode' => '13'],
            ['name' => 'Perjanjian Kerja (PK)', 'kode' => '14'],
            ['name' => 'Surat Pengantar (SPeng)', 'kode' => '15'],
        ];

        foreach ($data as $item) {
            // Kita gunakan updateOrCreate agar jika dijalankan ulang tidak duplikat
            Category::updateOrCreate(
                ['name' => $item['name']], // Cari berdasarkan nama ini
                [
                    'kode_surat' => $item['kode'],
                    'slug' => Str::slug($item['name']),
                    'type' => 'letter' // Kategori baru ini kita set sebagai tipe 'letter' (surat)
                ]
            );
        }
    }
}