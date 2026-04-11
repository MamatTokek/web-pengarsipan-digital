<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Letter extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'uuid', // Tambahkan ini agar bisa di-update melalui Tinker atau proses simpan
        'letter_number',
        'name',
        'original_file_name',
        'category_id',
        'file_path',
        'uploaded_at',
        'need_action',
        'action_status',
        'admin_note',
        'reply_to_id',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime', 
        'need_action' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        // Logika otomatis isi UUID saat membuat data baru
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function parentLetter()
    {
        return $this->belongsTo(Letter::class, 'reply_to_id');
    }

    public function replyLetter()
    {
        return $this->hasOne(Letter::class, 'reply_to_id');
    }
    
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getPublicUrlAttribute()
    {
        return Storage::url($this->file_path);
    }
}