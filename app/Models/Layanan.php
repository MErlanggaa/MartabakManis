<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Layanan extends Model
{
    protected $table = 'layanan';
    
    protected $fillable = [
        'user_id',
        'nama',
        'price',
        'photo_path',
        'description',
        'views',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    // Relasi belongsTo dengan User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi many-to-many dengan UMKM
    public function umkm()
    {
        return $this->belongsToMany(UMKM::class, 'layanan_umkm', 'layanan_id', 'umkm_id');
    }

    // Relasi dengan Comments
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
