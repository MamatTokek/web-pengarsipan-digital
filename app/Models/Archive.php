<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Archive extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'uuid',
        'letter_number',
        'name',
        'description', // TAMBAHKAN KOLOM INI
        'original_file_name',
        'category_id',
        'file_path',
        'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime', 
    ];

    protected static function boot()
    {
        parent::boot();

        // Otomatis isi UUID saat data dibuat
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }
    
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Accessor untuk URL publik (seperti yang sudah kita perbaiki sebelumnya)
    public function getPublicUrlAttribute()
    {
        $relativePath = str_replace('public/', '', $this->file_path);
        return asset('storage/' . $relativePath); 
    }
}