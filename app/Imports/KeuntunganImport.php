<?php

namespace App\Imports;

use App\Models\Keuntungan;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class KeuntunganImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $umkmId;

    public function __construct($umkmId)
    {
        $this->umkmId = $umkmId;
    }

    public function model(array $row)
    {
        $pendapatan = $row['pendapatan'] ?? 0;
        $pengeluaran = $row['pengeluaran'] ?? 0;
        $keuntunganBersih = $row['keuntungan_bersih'] ?? ($pendapatan - $pengeluaran);
        
        // Convert date to month format for storage
        $tanggal = $row['tanggal'] ?? $row['bulan'] ?? date('Y-m-d');
        $bulan = date('F Y', strtotime($tanggal));

        return new Keuntungan([
            'umkm_id' => $this->umkmId,
            'bulan' => $bulan,
            'pendapatan' => $pendapatan,
            'pengeluaran' => $pengeluaran,
            'keuntungan_bersih' => $keuntunganBersih,
            'jumlah_transaksi' => $row['jumlah_transaksi'] ?? 0,
        ]);
    }

    public function rules(): array
    {
        return [
            'tanggal' => 'required|date',
            'pendapatan' => 'required|numeric|min:0',
            'pengeluaran' => 'required|numeric|min:0',
            'keuntungan_bersih' => 'nullable|numeric',
            'jumlah_transaksi' => 'nullable|integer|min:0',
        ];
    }
}
