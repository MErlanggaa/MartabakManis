<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'umkm_id',
        'layanan_id',
        'comment',
        'rating',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        ];
    }

    // Relasi dengan User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi dengan UMKM
    public function umkm()
    {
        return $this->belongsTo(UMKM::class);
    }

    // Relasi dengan Layanan
    public function layanan()
    {
        return $this->belongsTo(Layanan::class);
    }
}
