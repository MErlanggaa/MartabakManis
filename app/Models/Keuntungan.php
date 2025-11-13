<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Keuntungan extends Model
{
    protected $table = 'keuntungan';
    
    protected $fillable = [
        'umkm_id',
        'bulan',
        'pendapatan',
        'pengeluaran',
        'keuntungan_bersih',
        'jumlah_transaksi',
    ];

    protected function casts(): array
    {
        return [
            'pendapatan' => 'decimal:2',
            'pengeluaran' => 'decimal:2',
            'keuntungan_bersih' => 'decimal:2',
        ];
    }

    // Relasi dengan UMKM
    public function umkm()
    {
        return $this->belongsTo(UMKM::class, 'umkm_id');
    }
}
