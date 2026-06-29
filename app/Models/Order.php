<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public const APP_TAX_RATE = 0.02;
    public const QRIS_TAX = 1000;

    public const DELIVERY_FEES = [
        'gojek' => 15000,
        'grab' => 14000,
        'umkm_go' => 10000,
    ];

    public const DELIVERY_LABELS = [
        'gojek' => 'Gojek (GoSend)',
        'grab' => 'Grab (GrabSend)',
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
        'qris_tax',
        'unique_code',
        'total',
        'customer_name',
        'customer_phone',
        'customer_address',
        'notes',
        'payment_status',
        'payment_method',
        'order_status',
        'midtrans_order_id',
        'midtrans_transaction_id',
        'is_seen_by_umkm',
        'driver_name',
        'driver_phone',
        'driver_code',
        'driver_photo',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'app_tax' => 'decimal:2',
            'qris_tax' => 'decimal:2',
            'unique_code' => 'integer',
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

    public function rating()
    {
        return $this->hasOne(OrderRating::class);
    }

    public function chats()
    {
        return $this->hasMany(OrderChat::class)->orderBy('created_at');
    }

    public static function calculatePricing(float $unitPrice, int $quantity, string $deliveryMethod, string $paymentMethod = 'midtrans', float $distance = 5.5, float $weight = 1.0, float $height = 10.0): array
    {
        $subtotal = $unitPrice * $quantity;
        
        $baseFee = 0;
        $weightMultiplier = 0;
        $heightMultiplier = 0;
        $minFee = 0;

        if ($deliveryMethod === 'gojek') {
            $baseFee = 3000 * $distance;
            $weightMultiplier = 1500;
            $heightMultiplier = 200;
            $minFee = 10000;
        } elseif ($deliveryMethod === 'grab') {
            $baseFee = 2800 * $distance;
            $weightMultiplier = 1300;
            $heightMultiplier = 185;
            $minFee = 10000;
        } else { // umkm_go
            $baseFee = 1800 * $distance;
            $weightMultiplier = 1000;
            $heightMultiplier = 120;
            $minFee = 6000;
        }

        $deliveryFee = max($minFee, round($baseFee + ($weight * $weightMultiplier) + ($height * $heightMultiplier)));

        $appTax = round($subtotal * self::APP_TAX_RATE, 2);
        $qrisTax = $paymentMethod === 'qris' ? self::QRIS_TAX : 0;
        $total = $subtotal + $deliveryFee + $appTax + $qrisTax;

        return compact('subtotal', 'deliveryFee', 'appTax', 'qrisTax', 'total');
    }
}
