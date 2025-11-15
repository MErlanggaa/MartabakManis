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
        Schema::table('umkm', function (Blueprint $table) {
            $table->string('instagram_url')->nullable()->after('no_wa');
            $table->string('shopee_url')->nullable()->after('instagram_url');
            $table->string('tokopedia_url')->nullable()->after('shopee_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('umkm', function (Blueprint $table) {
            $table->dropColumn(['instagram_url', 'shopee_url', 'tokopedia_url']);
        });
    }
};
