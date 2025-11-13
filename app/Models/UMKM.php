<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UMKM extends Model
{
    protected $table = 'umkm';
    
    protected $fillable = [
        'user_id',
        'nama',
        'description',
        'favorite',
        'latitude',
        'longitude',
        'photo_path',
        'favorit_count',
        'jenis_umkm',
        'no_wa',
        'views',
    ];

    protected function casts(): array
    {
        return [
            'favorite' => 'array',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
        ];
    }

    // Relasi dengan User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi dengan LayananUMKM
    public function layananUmkm()
    {
        return $this->hasMany(LayananUMKM::class);
    }

    // Relasi dengan Keuntungan
    public function keuntungan()
    {
        return $this->hasMany(Keuntungan::class, 'umkm_id');
    }

    // Relasi many-to-many dengan Layanan
    public function layanan()
    {
        return $this->belongsToMany(Layanan::class, 'layanan_umkm', 'umkm_id', 'layanan_id');
    }

    // Relasi dengan Comments
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
