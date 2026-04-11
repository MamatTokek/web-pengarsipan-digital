<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    protected $fillable = ['user_id', 'description', 'subject_name', 'type'];

    // Relasi ke User agar kita bisa ambil nama dan role pelaku aktivitas
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function log($description, $subject, $type)
    {
        return self::create([
            'user_id' => auth()->id(),
            'description' => $description,
            'subject_name' => $subject,
            'type' => $type,
        ]);
    }
}

    