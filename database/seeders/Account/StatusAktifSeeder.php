<?php

namespace Database\Seeders\Account;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class StatusAktifSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $status = ['Non Aktif', 'Aktif', 'Dinonaktifkan'];
        foreach ($status as $status) {
            DB::table('status_aktifs')->insert([
                'label' => $status,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
