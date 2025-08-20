<?php

namespace Database\Seeders\Semarang;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use App\Helpers\KelurahanHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class KelurahanSeeder extends Seeder
{
    public function run(): void
    {
        $kelurahanData = KelurahanHelper::getKelurahanWithKecamatanId();

        foreach ($kelurahanData as $kelurahan) {
            $kelurahanId = DB::table('kelurahans')->insertGetId([
                'nama_kelurahan' => $kelurahan['nama_kelurahan'],
                'kode_kelurahan' => $kelurahan['kode_kelurahan'],
                'max_rw' => $kelurahan['max_rw'],
                'provinsi_id' => 1,
                'kabupaten_id' => 1,
                'jumlah_tps' => rand(1, 25),  // Jumlah TPS acak
                'kecamatan_id' => $kelurahan['kecamatan_id'],
                'created_at' => Carbon::now('Asia/Jakarta'),
                'updated_at' => Carbon::now('Asia/Jakarta'),
            ]);

            DB::table('upcoming_tps')->insert([
                'kelurahan_id' => $kelurahanId,
                'tahun' => 2024,
                'jumlah_tps' => rand(1, 25),
                'created_at' => Carbon::now('Asia/Jakarta'),
                'updated_at' => Carbon::now('Asia/Jakarta'),
            ]);
        }
    }
}
