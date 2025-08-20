<?php

namespace App\Imports\KPU;

use App\Models\Partai;
use App\Models\SuaraKPU;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\KategoriSuara;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class SuaraKPUImport implements ToModel, WithHeadingRow
{
    use Importable;

    private $Partai;
    private $Kelurahan;
    private $Kecamatan;
    private $KategoriSuara;

    public function __construct()
    {
        $this->Partai = Partai::select('id', 'nama')->get();
        $this->Kelurahan = Kelurahan::select('id', 'nama_kelurahan', 'kecamatan_id')->get();
        $this->Kecamatan = Kecamatan::select('id', 'nama_kecamatan')->get();
        $this->KategoriSuara = KategoriSuara::select('id', 'label')->get();
    }

    // public function rules(): array
    // {
    //     return [
    //         'partai' => 'required',
    //         'kelurahan' => 'required',
    //         'tahun' => 'required|integer',
    //         'tps' => 'required|integer',
    //         'kategori_suara' => 'required',
    //         'cakupan_wilayah' => 'required',
    //         'alamat' => 'required',
    //         'jumlah_suara' => 'required|integer',
    //         'dpt_laki' => 'required|integer',
    //         'dpt_perempuan' => 'required|integer',
    //         'jumlah_dpt' => 'required|integer',
    //         'suara_caleg' => 'nullable|integer',
    //         'suara_partai' => 'nullable|integer',
    //     ];
    // }

    // public function customValidationMessages()
    // {
    //     return [
    //         'partai.required' => 'Nama partai tidak boleh kosong.',
    //         'kelurahan.required' => 'Nama kelurahan tidak boleh kosong.',
    //         'tahun.required' => 'Tahun tidak boleh kosong.',
    //         'tahun.integer' => 'Tahun harus berupa angka.',
    //         'tps.required' => 'TPS tidak boleh kosong.',
    //         'tps.integer' => 'TPS harus berupa angka.',
    //         'cakupan_wilayah.required' => 'Cakupan wilayah TPS tidak boleh kosong.',
    //         'kategori_suara.required' => 'Kategori suara TPS tidak boleh kosong.',
    //         'alamat.required' => 'Alamat tidak boleh kosong.',
    //         'jumlah_suara.required' => 'Jumlah suara tidak boleh kosong.',
    //         'jumlah_suara.integer' => 'Jumlah suara harus berupa angka.',
    //         'dpt_laki.required' => 'Jumlah DPT laki-laki tidak boleh kosong.',
    //         'dpt_perempuan.required' => 'Jumlah DPT perempuan tidak boleh kosong.',
    //         'jumlah_dpt.required' => 'Jumlah DPT tidak boleh kosong.',
    //         'jumlah_dpt.integer' => 'Jumlah DPT harus berupa angka.',
    //         'suara_caleg.integer' => 'Suara caleg harus berupa angka.',
    //         'suara_partai.integer' => 'Suara partai harus berupa angka.',
    //     ];
    // }

    public function model(array $row)
    {
        $kecamatan = $this->Kecamatan->where('nama_kecamatan', ucwords(strtolower($row['kecamatan'])))->first();
        if (!$kecamatan) {
            throw new \Exception("Kecamatan '" . $row['kecamatan'] . "' tidak ditemukan.");
        }

        $kelurahan = $this->Kelurahan->where('nama_kelurahan', ucwords(strtolower($row['kelurahan'])))
            ->where('kecamatan_id', $kecamatan->id)->first();
        if (!$kelurahan) {
            throw new \Exception("Kelurahan '" . $row['kelurahan'] . "' tidak ditemukan.");
        }

        $kategori_suara = $this->KategoriSuara->where('label', $row['kategori_suara'])->first();
        if (!$kategori_suara) {
            throw new \Exception("Kategori '" . $row['kategori_suara'] . "' tidak ditemukan.");
        }

        for ($i = 0; $i < 18; $i++) {
            $key = $i;
            if ($i < 18) {
                $key = $i + 1;
            } else {
                $key = $i;
            }
            $partai = $this->Partai->where('nama', $row['partai_' . $key])->first();
            if (!$partai) {
                throw new \Exception("Partai '" . $row['partai_' . $key] . "' tidak ditemukan.");
            }

            SuaraKPU::create([
                'partai_id' => $partai->id,
                'kelurahan_id' => $kelurahan->id,
                'tahun' => $row['tahun'],
                'tps' => $row['tps'],
                'kategori_suara_id' => $kategori_suara->id,
                'cakupan_wilayah' => $row['cakupan_wilayah'],
                'alamat' => $row['alamat'],
                'jumlah_suara' => $row['total_suara_' . $key],
                'dpt_laki' => $row['dpt_laki'],
                'dpt_perempuan' => $row['dpt_perempuan'],
                'jumlah_dpt' => $row['total_dpt'],
                'suara_caleg' => $row['suara_caleg_' . $key] ?? null,
                'suara_partai' => $row['suara_partai_' . $key] ?? null,
            ]);
        }
    }
}
