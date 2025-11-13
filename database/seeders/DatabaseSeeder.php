<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::firstOrCreate(
            ['email' => 'admin@umkm.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]
        );

        // Create sample UMKM user
        User::firstOrCreate(
            ['email' => 'umkm@example.com'],
            [
                'name' => 'Pemilik UMKM',
                'password' => bcrypt('password'),
                'role' => 'umkm',
            ]
        );

        // Create sample user
        User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'User Biasa',
                'password' => bcrypt('password'),
                'role' => 'user',
            ]
        );

        // Seed layanan data
        $this->call(LayananSeeder::class);
    }
}
