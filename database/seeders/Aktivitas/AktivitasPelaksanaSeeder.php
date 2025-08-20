<?php

namespace Database\Seeders\Aktivitas;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Kelurahan;
use App\Models\StatusAktivitas;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AktivitasPelaksanaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('id', '!=', 1)->where('role_id', 3)->get();
        $statusAktivitas = StatusAktivitas::all();
        $kelurahans = Kelurahan::all();

        if ($users->isEmpty() || $statusAktivitas->isEmpty() || $kelurahans->isEmpty()) {
            return;
        }

        foreach ($users as $user) {
            // Tentukan bulan dan tahun yang sama (sebelum bulan saat ini, tahun ini)
            $bulan = rand(1, Carbon::now('Asia/Jakarta')->month - 1);
            $tahun = Carbon::now('Asia/Jakarta')->year;

            $jumlahAktivitas = rand(5, 10);

            for ($i = 0; $i < $jumlahAktivitas; $i++) {
                // Ambil status aktivitas dan kelurahan secara acak
                $status = $statusAktivitas->random();
                $kelurahan = $kelurahans->random();

                // Buat tanggal mulai dan selesai aktivitas secara acak di bulan dan tahun yang sama
                $tglMulai = Carbon::create($tahun, $bulan, rand(1, 28))->format('Y-m-d');
                $tglSelesai = Carbon::parse($tglMulai)->addDays(rand(1, 5))->format('Y-m-d');

                // Isi tabel aktivitas_pelaksanas
                DB::table('aktivitas_pelaksanas')->insert([
                    'pelaksana' => $user->id,
                    'status_aktivitas' => $status->id,
                    'deskripsi' => 'Deskripsi aktivitas oleh ' . $user->nama,
                    'tgl_mulai' => $tglMulai,
                    'tgl_selesai' => $tglSelesai,
                    'tempat_aktivitas' => 'Tempat aktivitas ' . $kelurahan->nama_kelurahan,
                    'foto_aktivitas' => 'default.jpg',
                    'rw' => rand(1, $kelurahan->max_rw),
                    'potensi_suara' => rand(100, 1000),
                    'kelurahan' => $kelurahan->id,
                    'created_at' => Carbon::now('Asia/Jakarta'),
                    'updated_at' => Carbon::now('Asia/Jakarta'),
                ]);
            }
        }
    }
}
