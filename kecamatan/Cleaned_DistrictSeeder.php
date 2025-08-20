<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DistrictSeeder extends Seeder
{
    public function run()
    {
        DB::table('districts')->insert([
            [
                'kode_kecamatan' => 'id3374160',
                'nama_kecamatan' => 'Ngaliyan',
                'provinsi_id' => 1,
                'kabupaten_id' => 1,
                'created_at' => Carbon::now('Asia/Jakarta'),
                'updated_at' => Carbon::now('Asia/Jakarta'),
            ],
            [
                'kode_kecamatan' => 'id3374120',
                'nama_kecamatan' => 'Semarang Utara',
                'provinsi_id' => 1,
                'kabupaten_id' => 1,
                'created_at' => Carbon::now('Asia/Jakarta'),
                'updated_at' => Carbon::now('Asia/Jakarta'),
            ],
            [
                'kode_kecamatan' => 'id3374090',
                'nama_kecamatan' => 'Genuk',
                'provinsi_id' => 1,
                'kabupaten_id' => 1,
                'created_at' => Carbon::now('Asia/Jakarta'),
                'updated_at' => Carbon::now('Asia/Jakarta'),
            ],
            [
                'kode_kecamatan' => 'id3374130',
                'nama_kecamatan' => 'Semarang Tengah',
                'provinsi_id' => 1,
                'kabupaten_id' => 1,
                'created_at' => Carbon::now('Asia/Jakarta'),
                'updated_at' => Carbon::now('Asia/Jakarta'),
            ],
            [
                'kode_kecamatan' => 'id3374030',
                'nama_kecamatan' => 'Banyumanik',
                'provinsi_id' => 1,
                'kabupaten_id' => 1,
                'created_at' => Carbon::now('Asia/Jakarta'),
                'updated_at' => Carbon::now('Asia/Jakarta'),
            ],
            [
                'kode_kecamatan' => 'id3374050',
                'nama_kecamatan' => 'Semarang Selatan',
                'provinsi_id' => 1,
                'kabupaten_id' => 1,
                'created_at' => Carbon::now('Asia/Jakarta'),
                'updated_at' => Carbon::now('Asia/Jakarta'),
            ],
            [
                'kode_kecamatan' => 'id3374040',
                'nama_kecamatan' => 'Gajah Mungkur',
                'provinsi_id' => 1,
                'kabupaten_id' => 1,
                'created_at' => Carbon::now('Asia/Jakarta'),
                'updated_at' => Carbon::now('Asia/Jakarta'),
            ],
            [
                'kode_kecamatan' => 'id3374140',
                'nama_kecamatan' => 'Semarang Barat',
                'provinsi_id' => 1,
                'kabupaten_id' => 1,
                'created_at' => Carbon::now('Asia/Jakarta'),
                'updated_at' => Carbon::now('Asia/Jakarta'),
            ],
            [
                'kode_kecamatan' => 'id3374010',
                'nama_kecamatan' => 'Mijen',
                'provinsi_id' => 1,
                'kabupaten_id' => 1,
                'created_at' => Carbon::now('Asia/Jakarta'),
                'updated_at' => Carbon::now('Asia/Jakarta'),
            ],
            [
                'kode_kecamatan' => 'id3374110',
                'nama_kecamatan' => 'Semarang Timur',
                'provinsi_id' => 1,
                'kabupaten_id' => 1,
                'created_at' => Carbon::now('Asia/Jakarta'),
                'updated_at' => Carbon::now('Asia/Jakarta'),
            ],
            [
                'kode_kecamatan' => 'id3374070',
                'nama_kecamatan' => 'Tembalang',
                'provinsi_id' => 1,
                'kabupaten_id' => 1,
                'created_at' => Carbon::now('Asia/Jakarta'),
                'updated_at' => Carbon::now('Asia/Jakarta'),
            ],
            [
                'kode_kecamatan' => 'id3374060',
                'nama_kecamatan' => 'Candisari',
                'provinsi_id' => 1,
                'kabupaten_id' => 1,
                'created_at' => Carbon::now('Asia/Jakarta'),
                'updated_at' => Carbon::now('Asia/Jakarta'),
            ],
            [
                'kode_kecamatan' => 'id3374020',
                'nama_kecamatan' => 'Gunung Pati',
                'provinsi_id' => 1,
                'kabupaten_id' => 1,
                'created_at' => Carbon::now('Asia/Jakarta'),
                'updated_at' => Carbon::now('Asia/Jakarta'),
            ],
            [
                'kode_kecamatan' => 'id3374100',
                'nama_kecamatan' => 'Gayamsari',
                'provinsi_id' => 1,
                'kabupaten_id' => 1,
                'created_at' => Carbon::now('Asia/Jakarta'),
                'updated_at' => Carbon::now('Asia/Jakarta'),
            ],
            [
                'kode_kecamatan' => 'id3374080',
                'nama_kecamatan' => 'Pedurungan',
                'provinsi_id' => 1,
                'kabupaten_id' => 1,
                'created_at' => Carbon::now('Asia/Jakarta'),
                'updated_at' => Carbon::now('Asia/Jakarta'),
            ],
            [
                'kode_kecamatan' => 'id3374150',
                'nama_kecamatan' => 'Tugu',
                'provinsi_id' => 1,
                'kabupaten_id' => 1,
                'created_at' => Carbon::now('Asia/Jakarta'),
                'updated_at' => Carbon::now('Asia/Jakarta'),
            ],
        ]);
    }
}
