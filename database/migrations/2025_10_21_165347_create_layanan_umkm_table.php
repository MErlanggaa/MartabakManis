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
        Schema::create('layanan_umkm', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('layanan_id');
            $table->unsignedBigInteger('umkm_id');
            $table->timestamps();
            
            $table->foreign('layanan_id')->references('id')->on('layanan')->onDelete('cascade');
            $table->foreign('umkm_id')->references('id')->on('umkm')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('layanan_umkm');
    }
};
