<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PartaiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $status = ['DEMOKRAT', 'PKB', 'GERINDRA', 'PDIP', 'GOLKAR', 'NASDEM', 'BURUH', 'GELORA', 'PKS', 'PKN', 'HANURA', 'GARUDA', 'PAN', 'PBB', 'PSI', 'PERINDO', 'PPP', 'UMAT'];
        $colors = ['002060', '006600', 'E26B0A', 'FF0000', 'FFFF00', '0F243E', 'FF9933', '00B0F0', 'FFC000', 'FF0066', 'E26B0A', 'FFFFFF', '00B0F0', '00B050', 'FF5050', 'CC0000', '006600', '222222'];

        foreach ($status as $index => $namaPartai) {
            DB::table('partais')->insert([
                'nama' => $namaPartai,
                'color' => $colors[$index],
                'created_at' => Carbon::now('Asia/Jakarta'),
                'updated_at' => Carbon::now('Asia/Jakarta'),
            ]);
        }
    }
}
