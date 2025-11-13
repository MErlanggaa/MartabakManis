<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('keuntungan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('umkm_id');
            $table->string('bulan');
            $table->decimal('pendapatan', 15, 2);
            $table->decimal('pengeluaran', 15, 2);
            $table->decimal('keuntungan_bersih', 15, 2);
            $table->integer('jumlah_transaksi');
            $table->timestamps();
            
            $table->foreign('umkm_id')->references('id')->on('umkm')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keuntungan');
    }
};
