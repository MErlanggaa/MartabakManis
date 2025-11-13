<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class KeuntunganTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return ['tanggal', 'pendapatan', 'pengeluaran', 'keuntungan_bersih', 'jumlah_transaksi'];
    }

    public function array(): array
    {
        return [
            ['2025-01-01', 100000, 50000, '=B2-C2', 5],
            ['2025-01-02', 150000, 75000, '=B3-C3', 8],
            ['2025-01-03', 200000, 100000, '=B4-C4', 12],
            ['2025-01-04', 120000, 60000, '=B5-C5', 6],
            ['2025-01-05', 180000, 90000, '=B6-C6', 10],
        ];
    }
}


