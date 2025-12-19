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
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('umkm_id')->constrained('umkm')->onDelete('cascade');
            $table->enum('type', ['topup', 'withdrawal']);
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('proof_path')->nullable(); // For topup
            $table->text('bank_info')->nullable(); // For withdrawal (JSON or Text)
            $table->text('admin_note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
