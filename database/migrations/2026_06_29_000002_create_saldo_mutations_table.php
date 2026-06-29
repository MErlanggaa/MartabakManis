<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saldo_mutations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('umkm_id');
            // credit = uang masuk, debit = uang keluar
            $table->enum('type', ['credit', 'debit']);
            // category of mutation
            $table->enum('category', [
                'order_income',    // pemasukan dari order lunas
                'withdrawal',      // penarikan dana ke rekening
                'admin_deduction', // potongan oleh admin (UMKM bandel)
                'refund',          // refund ke user
            ]);
            $table->decimal('amount', 15, 2);
            $table->string('description')->nullable();
            // optional references
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('withdraw_id')->nullable();
            $table->unsignedBigInteger('report_id')->nullable();
            // running balance after this mutation
            $table->decimal('balance_after', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('umkm_id')->references('id')->on('umkm')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
            $table->foreign('withdraw_id')->references('id')->on('withdraw_requests')->nullOnDelete();
            $table->foreign('report_id')->references('id')->on('reports')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saldo_mutations');
    }
};
