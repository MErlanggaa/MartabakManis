<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LayananUMKM extends Model
{
    protected $table = 'layanan_umkm';
    
    protected $fillable = [
        'layanan_id',
        'umkm_id',
    ];

    // Relasi dengan Layanan
    public function layanan()
    {
        return $this->belongsTo(Layanan::class);
    }

    // Relasi dengan UMKM
    public function umkm()
    {
        return $this->belongsTo(UMKM::class);
    }
}
