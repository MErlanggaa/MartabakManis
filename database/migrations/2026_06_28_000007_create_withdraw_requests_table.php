<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('withdraw_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('umkm_id')->constrained('umkm')->onDelete('cascade');
            $table->decimal('jumlah', 15, 2); // jumlah yang ingin ditarik
            $table->string('rekening_bank', 100); // nama bank (BCA, BRI, dll)
            $table->string('nomor_rekening', 50); // nomor rekening
            $table->string('nama_pemilik', 255); // nama pemilik rekening
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_note')->nullable(); // catatan admin
            $table->string('bukti_transfer')->nullable(); // path foto bukti transfer
            $table->timestamp('processed_at')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('is_seen_by_admin')->default(false); // untuk notifikasi admin
            $table->boolean('is_seen_by_umkm')->default(true); // untuk notifikasi UMKM (false = belum lihat hasil)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdraw_requests');
    }
};
