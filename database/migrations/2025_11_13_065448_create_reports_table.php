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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('email');
            $table->enum('kategori', ['bug', 'fitur', 'pertanyaan', 'lainnya']);
            $table->string('judul');
            $table->text('deskripsi');
            $table->enum('status', ['pending', 'diproses', 'selesai'])->default('pending');
            $table->text('respon_admin')->nullable();
            $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Index untuk performa query
            $table->index('status');
            $table->index('kategori');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
