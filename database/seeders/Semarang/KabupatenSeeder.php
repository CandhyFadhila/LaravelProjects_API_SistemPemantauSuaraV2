<?php

namespace Database\Seeders\Semarang;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class KabupatenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kabupaten_kota = [
            'nama_kabupaten' => 'Kota Semarang',
            'provinsi_id' => 1,
            'created_at' => Carbon::now('Asia/Jakarta'),
            'updated_at' => Carbon::now('Asia/Jakarta'),
        ];
        DB::table('kabupatens')->insert($kabupaten_kota);
    }
}
