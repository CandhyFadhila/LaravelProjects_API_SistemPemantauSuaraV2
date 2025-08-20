<?php

namespace Database\Seeders;

use App\Helpers\RandomHelper;
use App\Models\User;
use App\Models\Kelurahan;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create('id_ID');
        $kelurahanIds = Kelurahan::pluck('id')->toArray();

        // Pertama, buat pengguna dengan role_id = 2 (Penanggung Jawab)
        for ($i = 1; $i <= 10; $i++) {
            $penanggungJawab = User::create([
                'nama' => $faker->name,
                'username' => $faker->unique()->userName,
                'nik_ktp' => $faker->unique()->numerify('###############'),
                'foto_profil' => null,
                'no_hp' => $faker->phoneNumber,
                'tgl_diangkat' => $faker->date(),
                'jenis_kelamin' => $faker->randomElement([0, 1]), // 0 = perempuan, 1 = laki-laki
                'kelurahan_id' => $faker->randomElements($kelurahanIds, rand(1, 3)), // Pilih 1-3 kelurahan secara acak
                'role_id' => 2, // Penanggung Jawab
                'password' => Hash::make('password123'),
                'status_aktif' => 2
            ]);

            $role = Role::where('name', 'Penanggung Jawab')->first();
            if ($role) {
                $penanggungJawab->assignRole($role);
            }
        }

        // Dapatkan pengguna Penanggung Jawab dengan kelurahan_id
        $penanggungJawabUsers = User::where('role_id', 2)->get(['id', 'kelurahan_id'])->toArray();

        // Kemudian, buat pengguna dengan role_id = 3 (Penggerak)
        for ($i = 1; $i <= 70; $i++) {
            $jenisKelamin = $faker->randomElement([0, 1]); // 0 = perempuan, 1 = laki-laki

            // Tentukan Penanggung Jawab secara acak dari daftar Penanggung Jawab
            $pjPelaksana = $faker->randomElement($penanggungJawabUsers);
            $kelurahanId = $pjPelaksana['kelurahan_id']; // Kelurahan diambil dari Penanggung Jawab

            $rwPelaksana = $faker->randomElements(range(1, 10), rand(1, 3));
            $namaFaker = $faker->name($jenisKelamin ? 'female' : 'male');
            $username = RandomHelper::generateUsername($namaFaker);

            $user = User::create([
                'nama' => $namaFaker,
                'username' => $username,
                'nik_ktp' => $faker->unique()->numerify('###############'),
                'foto_profil' => null,
                'no_hp' => $faker->phoneNumber,
                'tgl_diangkat' => $faker->date(),
                'jenis_kelamin' => $jenisKelamin,
                'role_id' => 3,
                'kelurahan_id' => $kelurahanId, // Kelurahan diambil dari Penanggung Jawab
                'rw_pelaksana' => $rwPelaksana,
                'pj_pelaksana' => $pjPelaksana['id'], // Penanggung Jawab Pelaksana
                'password' => Hash::make('password123'),
                'status_aktif' => 2
            ]);

            $role = Role::where('name', 'Penggerak')->first();
            if ($role) {
                $user->assignRole($role);
            }
        }
    }
}

