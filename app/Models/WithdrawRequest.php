<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WithdrawRequest extends Model
{
    protected $table = 'withdraw_requests';

    protected $fillable = [
        'umkm_id',
        'jumlah',
        'rekening_bank',
        'nomor_rekening',
        'nama_pemilik',
        'status',
        'admin_note',
        'bukti_transfer',
        'processed_at',
        'processed_by',
        'is_seen_by_admin',
        'is_seen_by_umkm',
    ];

    protected function casts(): array
    {
        return [
            'jumlah'            => 'decimal:2',
            'is_seen_by_admin'  => 'boolean',
            'is_seen_by_umkm'   => 'boolean',
            'processed_at'      => 'datetime',
        ];
    }

    public function umkm()
    {
        return $this->belongsTo(UMKM::class, 'umkm_id');
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending'  => 'Menunggu',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default    => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending'  => 'amber',
            'approved' => 'green',
            'rejected' => 'red',
            default    => 'gray',
        };
    }
}
