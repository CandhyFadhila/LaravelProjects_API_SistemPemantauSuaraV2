<?php

namespace Database\Seeders\Account;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use App\Helpers\PermissionHelper;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $created_at = Carbon::now('Asia/Jakarta')->subDays(rand(0, 365));
        $updated_at = Carbon::now('Asia/Jakarta');

        $roleSuperAdmin = Role::create([
            'name' => 'Super Admin',
            'deskripsi' => 'Ini adalah role paling GG',
            'created_at' => $created_at,
            'updated_at' => $updated_at,
        ]);
        $roleSuperAdmin->givePermissionTo(PermissionHelper::getPermissionsByRole(1));

        $rolePJ = Role::create([
            'name' => 'Penanggung Jawab',
            'deskripsi' => 'Ini adalah role penanggung jawab',
            'created_at' => $created_at,
            'updated_at' => $updated_at,
        ]);
        $rolePJ->givePermissionTo(PermissionHelper::getPermissionsByRole(2));

        $rolePenggerak = Role::create([
            // 'name' => 'Pelaksana',
            'name' => 'Penggerak',
            'deskripsi' => 'Ini adalah role Penggerak',
            'created_at' => $created_at,
            'updated_at' => $updated_at,
        ]);
        $rolePenggerak->givePermissionTo(PermissionHelper::getPermissionsByRole(3));
    }
}
