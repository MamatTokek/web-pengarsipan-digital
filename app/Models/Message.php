<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = ['sender_id', 'receiver_id', 'target_role', 'subject', 'body', 'document_id', 'document_type', 'is_read'];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function readers()
    {
        // Relasi ke user yang sudah membaca melalui tabel pivot
        return $this->belongsToMany(User::class, 'message_reads');
    }

    public function isReadBy($userId)
    {
        return $this->readers()->where('user_id', $userId)->exists();
    }
}
