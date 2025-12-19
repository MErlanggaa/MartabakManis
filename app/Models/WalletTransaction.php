<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    protected $fillable = [
        'transaction_code',
        'umkm_id',
        'type',
        'amount',
        'status',
        'proof_path',
        'bank_info',
        'admin_note',
    ];

    public function umkm()
    {
        return $this->belongsTo(UMKM::class, 'umkm_id');
    }
}
