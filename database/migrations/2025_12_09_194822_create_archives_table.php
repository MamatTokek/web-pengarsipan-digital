<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('archives', function (Blueprint $table) {
            $table->id();
            // Nama Dokumen (sama dengan name di letters)
            $table->string('name');
            // Deskripsi (kolom baru untuk arsip)
            $table->text('description')->nullable(); 
            // Nama file asli
            $table->string('original_file_name');
            // Path file di storage
            $table->string('file_path');
            // Relasi ke Kategori
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            // Waktu upload
            $table->timestamp('uploaded_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archives');
    }
};
