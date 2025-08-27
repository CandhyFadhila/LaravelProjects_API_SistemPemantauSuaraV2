<?php

namespace App\Exports\Saksi;

use App\Models\AktivitasSaksi;
use App\Models\User;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AktivitasSaksiExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    protected $User;

    public function __construct($User)
    {
        $this->User = $User;
    }

    public function collection()
    {
        $query = AktivitasSaksi::with(['saksi_users', 'status', 'kelurahans']);

        if ($this->User->role_id == 1) {
            return $query->orderBy('tgl_mulai', 'asc')->get();
        }
        if ($this->User->role_id == 2) {
            return $query->whereHas('saksi_users', function ($q) {
                $q->where('role_id', 3)->where('pj_pelaksana', $this->User->id)
                    ->orWhere('saksi', $this->User->id);
            })->orderBy('tgl_mulai', 'asc')->get();
        }
        if ($this->User->role_id == 3) {
            return $query->where('saksi', $this->User->id)
                ->orderBy('tgl_mulai', 'asc')->get();
        }
        if ($this->User->role_id == 4) {
            return $query->where('saksi', $this->User->id)
                ->orderBy('tgl_mulai', 'asc')->get();
        }

        return collect();
    }

    public function headings(): array
    {
        return [
            'no',
            'saksi',
            'penanggung_jawab',
            'deskripsi_aktivitas',
            'tanggal_mulai',
            'tanggal_selesai',
            'tempat_aktivitas',
            'rw',
            'kelurahan',
            'kecamatan',
            'status_aktivitas',
            'tps',
            'terakhir_dibuat',
            'terakhir_diperbarui'
        ];
    }

    public function map($aktivitasSaksi): array
    {
        static $no = 1;

        $pjPelaksana = $aktivitasSaksi->saksi_users && $aktivitasSaksi->saksi_users->pj_pelaksana
            ? User::find($aktivitasSaksi->saksi_users->pj_pelaksana)
            : null;
        $pjPelaksanaNama = $pjPelaksana ? $pjPelaksana->nama : 'N/A';

        return [
            $no++,
            $aktivitasSaksi->saksi_users ? $aktivitasSaksi->saksi_users->nama : 'N/A',
            $pjPelaksanaNama,
            $aktivitasSaksi->deskripsi ?? 'N/A',
            $aktivitasSaksi->tgl_mulai,
            $aktivitasSaksi->tgl_selesai,
            $aktivitasSaksi->tempat_aktivitas,
            $aktivitasSaksi->rw,
            $aktivitasSaksi->kelurahans ? $aktivitasSaksi->kelurahans->nama_kelurahan : 'N/A',
            $aktivitasSaksi->kelurahans->kecamatans ? $aktivitasSaksi->kelurahans->kecamatans->nama_kecamatan : 'N/A',
            $aktivitasSaksi->status ? $aktivitasSaksi->status->label : 'N/A',
            $aktivitasSaksi->tps ?? 'N/A',
            $aktivitasSaksi->created_at,
            $aktivitasSaksi->updated_at
        ];
    }
}
