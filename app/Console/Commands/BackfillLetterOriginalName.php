<?php

namespace App\Console\Commands;

use App\Models\Letter; // Pastikan Model Letter di-import
use Illuminate\Console\Command;

class BackfillLetterOriginalName extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:backfill-letter-original-name';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting backfill for original file names...');

        // Ambil semua surat yang kolom original_file_name-nya NULL
        $letters = Letter::whereNull('original_file_name')->get();
        
        $count = 0;
        foreach ($letters as $letter) {
            // Logika untuk membersihkan nama file
            // Contoh: Hapus 'public/letters/' dan hapus timestamp di depannya
            $fileNameWithTimestamp = basename($letter->file_path);
            $underscorePos = strpos($fileNameWithTimestamp, '_');
            
            if ($underscorePos !== false) {
                $originalName = substr($fileNameWithTimestamp, $underscorePos + 1);
                
                // Simpan nilai baru
                $letter->original_file_name = $originalName;
                $letter->save();
                $count++;
            }
        }

        $this->info("Successfully updated {$count} letters.");
        return Command::SUCCESS;
    }
}
