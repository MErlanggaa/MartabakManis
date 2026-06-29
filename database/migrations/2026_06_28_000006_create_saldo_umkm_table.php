<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saldo_umkm', function (Blueprint $table) {
            $table->id();
            $table->foreignId('umkm_id')->constrained('umkm')->onDelete('cascade');
            $table->decimal('saldo_tersedia', 15, 2)->default(0); // Saldo yang bisa di-WD
            $table->decimal('total_pemasukan', 15, 2)->default(0); // Total kumulatif pendapatan online (tidak berkurang)
            $table->decimal('total_withdraw', 15, 2)->default(0); // Total yang sudah ditarik
            $table->timestamps();

            $table->unique('umkm_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saldo_umkm');
    }
};
