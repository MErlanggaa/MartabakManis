<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaldoMutation extends Model
{
    protected $table = 'saldo_mutations';

    protected $fillable = [
        'umkm_id',
        'type',
        'category',
        'amount',
        'description',
        'order_id',
        'withdraw_id',
        'report_id',
        'balance_after',
    ];

    protected function casts(): array
    {
        return [
            'amount'        => 'decimal:2',
            'balance_after' => 'decimal:2',
        ];
    }

    public function umkm()
    {
        return $this->belongsTo(UMKM::class, 'umkm_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function withdraw()
    {
        return $this->belongsTo(WithdrawRequest::class, 'withdraw_id');
    }

    public function report()
    {
        return $this->belongsTo(Report::class, 'report_id');
    }

    /**
     * Record income from a paid order (net = subtotal).
     */
    public static function recordOrderIncome(int $umkmId, Order $order): self
    {
        $netAmount = (float) $order->subtotal;

        // Calculate current balance dynamically
        $currentBalance = self::computeCurrentBalance($umkmId);
        $balanceAfter = $currentBalance + $netAmount;

        return self::create([
            'umkm_id'      => $umkmId,
            'type'         => 'credit',
            'category'     => 'order_income',
            'amount'       => $netAmount,
            'description'  => "Pemasukan dari order #{$order->order_code} - {$order->customer_name}",
            'order_id'     => $order->id,
            'balance_after'=> $balanceAfter,
        ]);
    }

    /**
     * Record a withdrawal debit.
     */
    public static function recordWithdrawal(int $umkmId, WithdrawRequest $wd): self
    {
        $currentBalance = self::computeCurrentBalance($umkmId);
        $balanceAfter = $currentBalance - (float) $wd->jumlah;

        return self::create([
            'umkm_id'      => $umkmId,
            'type'         => 'debit',
            'category'     => 'withdrawal',
            'amount'       => (float) $wd->jumlah,
            'description'  => "Penarikan dana ke {$wd->rekening_bank} a/n {$wd->nama_pemilik}",
            'withdraw_id'  => $wd->id,
            'balance_after'=> $balanceAfter,
        ]);
    }

    /**
     * Record admin deduction (punishment for bad UMKM).
     */
    public static function recordAdminDeduction(int $umkmId, float $amount, string $reason, ?int $reportId = null): self
    {
        $currentBalance = self::computeCurrentBalance($umkmId);
        $balanceAfter = $currentBalance - $amount;

        return self::create([
            'umkm_id'      => $umkmId,
            'type'         => 'debit',
            'category'     => 'admin_deduction',
            'amount'       => $amount,
            'description'  => "Pemotongan admin: {$reason}",
            'report_id'    => $reportId,
            'balance_after'=> $balanceAfter,
        ]);
    }

    /**
     * Record refund (subtract money from UMKM balance, can go negative).
     */
    public static function recordRefund(int $umkmId, Order $order): self
    {
        $currentBalance = self::computeCurrentBalance($umkmId);
        $amount = (float) $order->subtotal;
        $balanceAfter = $currentBalance - $amount;

        return self::create([
            'umkm_id'      => $umkmId,
            'type'         => 'debit',
            'category'     => 'refund',
            'amount'       => $amount,
            'description'  => "Refund / retur pesanan #{$order->order_code} - {$order->customer_name}",
            'order_id'     => $order->id,
            'balance_after'=> $balanceAfter,
        ]);
    }

    /**
     * Compute current saldo from orders - approved withdrawals - admin deductions.
     * Same logic as WithdrawController::getSaldo() but returns a float.
     */
    public static function computeCurrentBalance(int $umkmId): float
    {
        $totalIncome = (float) Order::where('umkm_id', $umkmId)
            ->where('payment_status', 'paid')
            ->sum('subtotal');

        $totalApprovedWd = (float) WithdrawRequest::where('umkm_id', $umkmId)
            ->where('status', 'approved')
            ->sum('jumlah');

        // Also subtract admin deductions recorded in mutations
        $totalDeductions = (float) self::where('umkm_id', $umkmId)
            ->where('category', 'admin_deduction')
            ->sum('amount');

        // Also subtract refunds
        $totalRefunds = (float) self::where('umkm_id', $umkmId)
            ->where('category', 'refund')
            ->sum('amount');

        return $totalIncome - $totalApprovedWd - $totalDeductions - $totalRefunds;
    }

    /**
     * Get readable category label.
     */
    public function getCategoryLabelAttribute(): string
    {
        return match($this->category) {
            'order_income'    => 'Pemasukan Order',
            'withdrawal'      => 'Penarikan Dana',
            'admin_deduction' => 'Pemotongan Admin',
            'refund'          => 'Refund',
            default           => $this->category,
        };
    }

    /**
     * Get badge color for type.
     */
    public function getTypeBadgeAttribute(): string
    {
        return $this->type === 'credit' ? 'green' : 'red';
    }
}
