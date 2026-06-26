<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public const APP_TAX_RATE = 0.02;

    public const DELIVERY_FEES = [
        'gojek' => 15000,
        'grab' => 14000,
        'umkm_go' => 10000,
    ];

    public const DELIVERY_LABELS = [
        'gojek' => 'Gojek',
        'grab' => 'Grab',
        'umkm_go' => 'UMKM.go',
    ];

    protected $fillable = [
        'order_code',
        'user_id',
        'umkm_id',
        'layanan_id',
        'quantity',
        'subtotal',
        'delivery_method',
        'delivery_fee',
        'app_tax',
        'total',
        'customer_name',
        'customer_phone',
        'customer_address',
        'notes',
        'payment_status',
        'order_status',
        'midtrans_order_id',
        'midtrans_transaction_id',
        'is_seen_by_umkm',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'app_tax' => 'decimal:2',
            'total' => 'decimal:2',
            'is_seen_by_umkm' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function umkm()
    {
        return $this->belongsTo(UMKM::class, 'umkm_id');
    }

    public function layanan()
    {
        return $this->belongsTo(Layanan::class, 'layanan_id');
    }

    public static function calculatePricing(float $unitPrice, int $quantity, string $deliveryMethod): array
    {
        $subtotal = $unitPrice * $quantity;
        $deliveryFee = self::DELIVERY_FEES[$deliveryMethod] ?? 0;
        $appTax = round($subtotal * self::APP_TAX_RATE, 2);
        $total = $subtotal + $deliveryFee + $appTax;

        return compact('subtotal', 'deliveryFee', 'appTax', 'total');
    }
}
