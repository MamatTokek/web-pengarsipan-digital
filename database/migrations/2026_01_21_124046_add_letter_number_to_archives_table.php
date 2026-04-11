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
        Schema::table('archives', function (Blueprint $table) {
            // Kita gunakan nullable() agar tidak wajib diisi
            // Kita gunakan unique() agar jika diisi, tidak boleh ada yang sama
            $table->string('letter_number')->unique()->nullable()->after('uuid');
        });
    }

    public function down(): void
    {
        Schema::table('archives', function (Blueprint $table) {
            $table->dropColumn('letter_number');
        });
    }
};
