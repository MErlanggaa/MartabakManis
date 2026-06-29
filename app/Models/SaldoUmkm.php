<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaldoUmkm extends Model
{
    protected $table = 'saldo_umkm';

    protected $fillable = [
        'umkm_id',
        'saldo_tersedia',
        'total_pemasukan',
        'total_withdraw',
    ];

    protected function casts(): array
    {
        return [
            'saldo_tersedia'  => 'decimal:2',
            'total_pemasukan' => 'decimal:2',
            'total_withdraw'  => 'decimal:2',
        ];
    }

    public function umkm()
    {
        return $this->belongsTo(UMKM::class, 'umkm_id');
    }

    /**
     * Credit saldo ketika ada order paid.
     */
    public static function creditFromOrder(int $umkmId, float $amount): self
    {
        $saldo = self::firstOrCreate(
            ['umkm_id' => $umkmId],
            ['saldo_tersedia' => 0, 'total_pemasukan' => 0, 'total_withdraw' => 0]
        );

        $saldo->increment('saldo_tersedia', $amount);
        $saldo->increment('total_pemasukan', $amount);

        return $saldo->fresh();
    }

    /**
     * Debit saldo ketika WD diapprove.
     */
    public static function debitForWithdraw(int $umkmId, float $amount): bool
    {
        $saldo = self::where('umkm_id', $umkmId)->first();
        if (!$saldo || $saldo->saldo_tersedia < $amount) {
            return false;
        }

        $saldo->decrement('saldo_tersedia', $amount);
        $saldo->increment('total_withdraw', $amount);

        return true;
    }

    /**
     * Hitung pemasukan hari ini dari orders.
     */
    public static function getPemasukanHariIni(int $umkmId): float
    {
        return (float) \App\Models\Order::where('umkm_id', $umkmId)
            ->where('payment_status', 'paid')
            ->whereDate('updated_at', today())
            ->sum('subtotal');
    }
}
