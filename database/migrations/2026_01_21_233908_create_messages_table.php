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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            // Pengirim
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            
            // Penerima (Bisa spesifik ke User ID tertentu)
            $table->foreignId('receiver_id')->nullable()->constrained('users')->onDelete('cascade');
            
            // Target Role (Jika ingin kirim ke "Semua Kades" atau "Semua Super Role")
            $table->string('target_role')->nullable(); 
        
            $table->string('subject');
            $table->text('body');
            
            // Opsional: Untuk menautkan pesan dengan dokumen tertentu yang ingin dihapus
            $table->unsignedBigInteger('document_id')->nullable();
            $table->string('document_type')->nullable(); // 'letter' atau 'archive'
        
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
