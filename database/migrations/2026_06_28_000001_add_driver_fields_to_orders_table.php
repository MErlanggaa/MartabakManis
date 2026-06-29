<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('driver_name')->nullable()->after('is_seen_by_umkm');
            $table->string('driver_phone')->nullable()->after('driver_name');
            $table->string('driver_code')->nullable()->after('driver_phone');
            $table->decimal('qris_tax', 12, 2)->default(0)->after('driver_code');
            $table->enum('payment_method', ['midtrans', 'qris'])->default('midtrans')->after('qris_tax');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['driver_name', 'driver_phone', 'driver_code', 'qris_tax', 'payment_method']);
        });
    }
};
