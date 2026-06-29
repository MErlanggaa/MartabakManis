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
        Schema::table('reports', function (Blueprint $table) {
            $table->integer('unread_by_user')->default(0)->after('status');
            $table->integer('unread_by_umkm')->default(0)->after('unread_by_user');
            $table->integer('unread_by_admin')->default(0)->after('unread_by_umkm');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn(['unread_by_user', 'unread_by_umkm', 'unread_by_admin']);
        });
    }
};
