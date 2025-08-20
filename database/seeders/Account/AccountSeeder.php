<?php

namespace Database\Seeders\Account;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roleSuperAdmin = User::create([
            'nama' => 'Super Admin',
            'username' => 'super.admin',
            'nik_ktp' => '1234567890123456',
            'no_hp' => '081234567890',
            'tgl_diangkat' => '2024-10-01',
            'status_aktif' => 2,
            'jenis_kelamin' => 1,
            'role_id' => 1,
            'password' => Hash::make('superadmin123'),
        ]);
        $roleSuperAdmin->assignRole('Super Admin');
    }
}
