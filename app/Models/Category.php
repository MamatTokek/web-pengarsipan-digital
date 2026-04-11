<?php

// app/Models/Category.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    
    protected $fillable = ['name', 'slug', 'type'];
    
    // Definisikan relasi ke Surat (opsional, tapi baik untuk kelengkapan)
    public function letters()
    {
        return $this->hasMany(Letter::class);
    }
}
