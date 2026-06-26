<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('umkm_id')->constrained('umkm')->cascadeOnDelete();
            $table->foreignId('layanan_id')->constrained('layanan')->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('subtotal', 12, 2);
            $table->enum('delivery_method', ['gojek', 'grab', 'umkm_go']);
            $table->decimal('delivery_fee', 12, 2)->default(0);
            $table->decimal('app_tax', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->text('customer_address');
            $table->text('notes')->nullable();
            $table->enum('payment_status', ['pending', 'paid', 'expired', 'failed', 'cancelled'])->default('pending');
            $table->enum('order_status', ['pending', 'confirmed', 'processing', 'delivered', 'cancelled'])->default('pending');
            $table->string('midtrans_order_id')->nullable();
            $table->string('midtrans_transaction_id')->nullable();
            $table->boolean('is_seen_by_umkm')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
