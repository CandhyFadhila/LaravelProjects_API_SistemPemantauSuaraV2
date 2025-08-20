<?php

namespace App\Exports\KPU;

use App\Models\SuaraKPU;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class SuaraKPUExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    public function collection()
    {
        return SuaraKPU::with(['partais', 'kelurahans', 'kelurahans.kecamatans'])->get();
    }

    public function headings(): array
    {
        return [
            'no',
            'partai',
            'kelurahan',
            'kecamatan',
            'tahun',
            'tps',
            'alamat',
            'cakupan_wilayah',
            'kategori_suara',
            'jumlah_suara',
            'dpt_laki',
            'dpt_perempuan',
            'jumlah_dpt',
            'suara_caleg',
            'suara_partai',
            'terakhir_dibuat',
            'terakhir_diperbarui'
        ];
    }

    public function map($suara_kpu): array
    {
        static $no = 1;

        return [
            $no++,
            $suara_kpu->partais ? $suara_kpu->partais->nama : 'N/A',
            $suara_kpu->kelurahans ? $suara_kpu->kelurahans->nama_kelurahan : 'N/A',
            $suara_kpu->kelurahans->kecamatans ? $suara_kpu->kelurahans->kecamatans->nama_kecamatan : 'N/A',
            $suara_kpu->tahun,
            $suara_kpu->tps,
            $suara_kpu->alamat,
            $suara_kpu->cakupan_wilayah,
            $suara_kpu->kategori_suaras ? $suara_kpu->kategori_suaras->label : 'N/A',
            $suara_kpu->jumlah_suara,
            $suara_kpu->dpt_laki,
            $suara_kpu->dpt_perempuan,
            $suara_kpu->jumlah_dpt,
            $suara_kpu->suara_caleg ?? 'N/A',
            $suara_kpu->suara_partai ?? 'N/A',
            $suara_kpu->created_at,
            $suara_kpu->updated_at
        ];
    }
}
