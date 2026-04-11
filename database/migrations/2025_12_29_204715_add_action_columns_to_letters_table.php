<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('letters', function (Blueprint $table) {
            // Menandai apakah butuh tindakan Kades
            $table->boolean('need_action')->default(false)->after('category_id');
            
            // Status tindakan: pending (menunggu), completed (selesai)
            $table->string('action_status')->nullable()->after('need_action');
            
            // Catatan dari Admin untuk Kepala Desa
            $table->text('admin_note')->nullable()->after('action_status');
            
            // Menghubungkan Surat Balasan ke Surat Masuk aslinya
            $table->foreignId('reply_to_id')
                  ->nullable()
                  ->after('admin_note')
                  ->constrained('letters')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('letters', function (Blueprint $table) {
            $table->dropForeign(['reply_to_id']);
            $table->dropColumn(['need_action', 'action_status', 'admin_note', 'reply_to_id']);
        });
    }
};