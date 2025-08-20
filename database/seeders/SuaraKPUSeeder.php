<?php

namespace Database\Seeders;

use App\Models\KategoriSuara;
use App\Models\Partai;
use App\Models\SuaraKPU;
use App\Models\Kelurahan;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SuaraKPUSeeder extends Seeder
{
    public function run(): void
    {
        $kelurahans = Kelurahan::select('id', 'jumlah_tps')->take(10)->get(); 
        $partaiIds = Partai::pluck('id')->toArray();
    
        foreach ($kelurahans as $kelurahan) {
            $totalTPS = $kelurahan->jumlah_tps;
    
            for ($tps = 1; $tps <= $totalTPS; $tps++) {
                foreach ($partaiIds as $partaiId) {
                    SuaraKPU::create([
                        'partai_id' => $partaiId,
                        'kelurahan_id' => $kelurahan->id,
                        'tahun' => 2024,
                        'tps' => $tps,
                        'kategori_suara_id' => 2,
                        'alamat' => 'Jalan Jalan',
                        'cakupan_wilayah' => 'Cakupan wilayah dari import',
                        'jumlah_suara' => rand(100, 500),
                        'dpt_laki' => rand(500, 1000),
                        'dpt_perempuan' => rand(500, 1000),
                        'jumlah_dpt' => rand(1000, 2000),
                        'suara_caleg' => rand(50, 250),
                        'suara_partai' => rand(50, 250),
                    ]);
                }
            }
        }
    }
    
}
