<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Layanan;

class LayananSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $layanan = [
            [
                'nama' => 'Konsultasi Bisnis',
                'price' => 500000,
                'photo_path' => null,
            ],
            [
                'nama' => 'Pelatihan Digital Marketing',
                'price' => 750000,
                'photo_path' => null,
            ],
            [
                'nama' => 'Desain Logo',
                'price' => 300000,
                'photo_path' => null,
            ],
            [
                'nama' => 'Pembuatan Website',
                'price' => 2000000,
                'photo_path' => null,
            ],
            [
                'nama' => 'Konsultasi Keuangan',
                'price' => 400000,
                'photo_path' => null,
            ],
            [
                'nama' => 'Pelatihan Manajemen',
                'price' => 600000,
                'photo_path' => null,
            ],
            [
                'nama' => 'Desain Kemasan',
                'price' => 250000,
                'photo_path' => null,
            ],
            [
                'nama' => 'Fotografi Produk',
                'price' => 400000,
                'photo_path' => null,
            ],
        ];

        foreach ($layanan as $item) {
            Layanan::create($item);
        }
    }
}
