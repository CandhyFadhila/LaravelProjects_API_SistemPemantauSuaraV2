<?php

namespace App\Exports\Pengguna;

use App\Models\User;
use App\Models\Kelurahan;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class PenggunaExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    protected $User;

    public function __construct($User)
    {
        $this->User = $User;
    }

    public function collection()
    {
        $query = User::with(['roles', 'status_users']);

        if ($this->User->role_id == 1) {
            return $query->where('id', '!=', 1)->get();
        }
        if ($this->User->role_id == 2) {
            return $query->where(function ($q) {
                $q->where('role_id', 3)->where('pj_pelaksana', $this->User->id)
                    ->orWhere('id', $this->User->id);
            })->get();
        }

        return collect();
    }

    public function headings(): array
    {
        return [
            'no',
            'nama',
            'username',
            'jenis_kelamin',
            'nik_ktp',
            'no_hp',
            'tanggal_diangkat',
            'status_aktif',
            'peran',
            'kelurahan',
            'rw_pelaksana',
            'pj_pelaksana',
            'terakhir_dibuat',
            'terakhir_diperbarui'
        ];
    }

    public function map($user): array
    {
        static $no = 1;
        $roles = $user->roles->pluck('name')->toArray();

        $kelurahanIds = $user->kelurahan_id ?? [];
        $namaKelurahans = Kelurahan::whereIn('id', $kelurahanIds)->pluck('nama_kelurahan')->toArray();
        $namaKelurahans = empty($namaKelurahans) ? 'N/A' : implode(', ', $namaKelurahans);

        $rwPelaksana = $user->rw_pelaksana ?? [];
        $rwPelaksanaStr = empty($rwPelaksana) ? 'N/A' : implode(', ', $rwPelaksana);

        $pjPelaksana = $user->pj_pelaksana ? User::find($user->pj_pelaksana) : null;
        $pjPelaksanaNama = $pjPelaksana ? $pjPelaksana->nama : 'N/A';

        return [
            $no++,
            $user->nama,
            $user->username,
            $user->jenis_kelamin ? 'Laki-laki' : 'Perempuan',
            $user->nik_ktp ?? 'N/A',
            $user->no_hp ?? 'N/A',
            $user->tgl_diangkat ?? 'N/A',
            $user->status_users->label,
            implode(', ', $roles),
            $namaKelurahans,
            $rwPelaksanaStr,
            $pjPelaksanaNama,
            $user->created_at,
            $user->updated_at
        ];
    }
}
