<?php

namespace App\Http\Controllers\Dashboard;

use App\Helpers\StatusAktivitasHelper;
use App\Models\SuaraKPU;
use App\Models\Kelurahan;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\AktivitasPelaksana;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\public\WithoutDataResource;
use App\Models\StatusAktivitasRw;
use App\Models\UpcomingTps;

class DetailMapController extends Controller
{
    public function indexPotensiSuara(Request $request)
    {
        try {
            if (!Gate::allows('view suaraKPU')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $kode_kelurahan = $request->input('kode_kelurahan', []);
            $tahun = $request->input('tahun', []);
            if (empty($kode_kelurahan) || empty($tahun)) {
                return response()->json([
                    'status' => Response::HTTP_BAD_REQUEST,
                    'message' => 'Kode kelurahan dan tahun diperlukan.',
                    'data' => null
                ], Response::HTTP_BAD_REQUEST);
            }

            // Cari kelurahan berdasarkan array kode_kelurahan
            $kelurahanIds = Kelurahan::whereIn('kode_kelurahan', $kode_kelurahan)->pluck('id');
            if ($kelurahanIds->isEmpty()) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Data kelurahan tidak ditemukan.',
                    'data' => null
                ], Response::HTTP_OK);
            }

            // Cari aktivitas berdasarkan id kelurahan dan filter tahun (tgl_mulai)
            $aktivitas = AktivitasPelaksana::whereIn('kelurahan', $kelurahanIds)
                ->where(function ($query) use ($tahun) {
                    foreach ($tahun as $year) {
                        $query->orWhereYear('tgl_mulai', $year);
                    }
                })
                ->get();
            if ($aktivitas->isEmpty()) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Data aktivitas tidak ditemukan untuk kelurahan ini di tahun yang dipilih.',
                    'data' => null
                ], Response::HTTP_OK);
            }

            $format_aktivitas = $aktivitas->map(function ($item) {
                return [
                    'id' => $item->id,
                    'pelaksana' => $item->pelaksana_users ? [
                        'id' => $item->pelaksana_users->id,
                        'nama' => $item->pelaksana_users->nama,
                        'username' => $item->pelaksana_users->username,
                        'nik_ktp' => $item->pelaksana_users->nik_ktp,
                        'foto_profil' =>  $item->pelaksana_users->foto_profil ? env('STORAGE_SERVER_DOMAIN') . $item->pelaksana_users->foto_profil : null,
                        'tgl_diangkat' => $item->pelaksana_users->tgl_diangkat,
                        'jenis_kelamin' => $item->pelaksana_users->jenis_kelamin,
                        'role_id' => $item->pelaksana_users->role_id,
                        'status_aktif' => $item->pelaksana_users->status_aktif,
                        'created_at' => $item->pelaksana_users->created_at,
                        'updated_at' => $item->pelaksana_users->updated_at
                    ] : null,
                    'status_aktivitas' => $item->status ? [
                        'id' => $item->status->id,
                        'label' => $item->status->label,
                        'created_at' => $item->status->created_at,
                        'updated_at' => $item->status->updated_at
                    ] : null,
                    'deskripsi' => $item->deskripsi,
                    'tgl_mulai' => $item->tgl_mulai,
                    'tgl_selesai' => $item->tgl_selesai,
                    'tempat_aktivitas' => $item->tempat_aktivitas,
                    'foto_aktivitas' => $item->foto_aktivitas ? env('STORAGE_SERVER_DOMAIN') . $item->foto_aktivitas : null,
                    'rw' => $item->rw,
                    'potensi_suara' => $item->potensi_suara,
                    'kelurahan' => $item->kelurahans ? [
                        'id' => $item->kelurahans->id,
                        'nama_kelurahan' => $item->kelurahans->nama_kelurahan,
                        'kode_kelurahan' => $item->kelurahans->kode_kelurahan,
                        'max_rw' => $item->kelurahans->max_rw,
                        'kecamatan' => $item->kelurahans->kecamatans,
                        'kabupaten' => $item->kelurahans->kabupaten_kotas,
                        'provinsi' => $item->kelurahans->provinsis,
                        'created_at' => $item->kelurahans->created_at,
                        'updated_at' => $item->kelurahans->updated_at
                    ] : null,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                ];
            })->values();

            $format_chart_2 = $aktivitas->map(function ($item) {
                return [
                    'rw' => $item->rw,
                    'potensi_suara' => $item->potensi_suara
                ];
            })->values();

            $statusAktivitasRw = StatusAktivitasRw::whereIn('kelurahan_id', $kelurahanIds)->get();
            foreach ($kelurahanIds as $kelurahanId) {
                $kelurahan = Kelurahan::find($kelurahanId);
                if ($kelurahan) {
                    $maxRw = $kelurahan->max_rw; // Dapatkan max_rw dari kelurahan
                    $rwList = array_fill(0, $maxRw, null); // Inisialisasi list dengan null

                    // Cek status untuk setiap RW
                    foreach ($statusAktivitasRw as $status) {
                        if ($status->kelurahan_id == $kelurahanId && $status->rw <= $maxRw) {
                            $rwList[$status->rw - 1] = $status->status_aktivitas; // Tampilkan status_aktivitas untuk RW yang sesuai
                        }
                    }

                    $transformed_rw_list = StatusAktivitasHelper::TransformRwList($rwList);
                }
            }

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'Data aktivitas dan chart berhasil ditampilkan.',
                'data' => [
                    'chart' => $transformed_rw_list,
                    'chart_2' => $format_chart_2,
                    'table' => $format_aktivitas,
                    'tahun' => $tahun
                ],
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Detail Map | - Error function indexPotensiSuara: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function indexSuaraKPU(Request $request)
    {
        try {
            if (!Gate::allows('view suaraKPU')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $kode_kelurahan = $request->input('kode_kelurahan', []);
            $tahun = $request->input('tahun', []);
            if (empty($kode_kelurahan) || empty($tahun)) {
                return response()->json([
                    'status' => Response::HTTP_BAD_REQUEST,
                    'message' => 'Kode kelurahan dan tahun diperlukan.',
                    'data' => null
                ], Response::HTTP_BAD_REQUEST);
            }

            // Step 1: Cari kelurahan berdasarkan array kode_kelurahan
            $kelurahanIds = Kelurahan::whereIn('kode_kelurahan', $kode_kelurahan)->pluck('id');
            if ($kelurahanIds->isEmpty()) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Data kelurahan tidak ditemukan.',
                    'data' => null
                ], Response::HTTP_OK);
            }

            // Step 2: Cari data suara KPU berdasarkan id kelurahan dan filter tahun
            $suaraKPU = SuaraKPU::whereIn('kelurahan_id', $kelurahanIds)
                ->where(function ($query) use ($tahun) {
                    foreach ($tahun as $year) {
                        $query->orWhere('tahun', $year);
                    }
                })
                ->get();
            if ($suaraKPU->isEmpty()) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Data suara KPU tidak ditemukan untuk kelurahan ini di tahun yang dipilih.',
                    'data' => null
                ], Response::HTTP_OK);
            }

            $upcomingTPS = UpcomingTps::whereIn('kelurahan_id', $kelurahanIds)
                ->whereIn('tahun', $tahun)
                ->first();
            if (!$upcomingTPS) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Data TPS mendatang tidak ditemukan untuk kelurahan ini di tahun yang dipilih.',
                    'data' => null
                ], Response::HTTP_OK);
            }

            $firstKelurahanId = $suaraKPU->first()->kelurahan_id;
            $groupedByPartai = $suaraKPU->where('kelurahan_id', $firstKelurahanId)->groupBy('partai_id');
            $format_suaraKPU = $groupedByPartai->map(function ($items, $partaiId) {
                $partai = $items->first()->partais;

                $tpsData = $items->map(function ($item) {
                    return [
                        'tps' => $item->tps,
                        'jumlah_suara' => $item->jumlah_suara,
                        'suara_caleg' => $item->suara_caleg ?? 'N/A',
                        'suara_partai' => $item->suara_partai ?? 'N/A',
                        'dpt_laki' => $item->dpt_laki,
                        'dpt_perempuan' => $item->dpt_perempuan,
                        'jumlah_dpt' => $item->jumlah_dpt
                    ];
                });

                return [
                    'partai' => [
                        'id' => $partai->id,
                        'nama' => $partai->nama,
                        'color' => $partai->color ?? null
                    ],
                    'tps' => $tpsData
                ];
            })->values();

            $format_chart = $suaraKPU->where('kelurahan_id', $firstKelurahanId)->groupBy('partai_id')->map(function ($items) {
                $partai = $items->first()->partais;
                $totalSuara = $items->sum('jumlah_suara');

                return [
                    'partai' => [
                        'id' => $partai->id,
                        'nama' => $partai->nama,
                        'color' => $partai->color ?? null
                    ],
                    'jumlah_suara' => $totalSuara
                ];
            })->values();

            $format_tps_mendatang = [
                'kelurahan' => $upcomingTPS->kelurahans ? [
                    'id' => $upcomingTPS->kelurahans->id,
                    'nama_kelurahan' => $upcomingTPS->kelurahans->nama_kelurahan,
                    'kode_kelurahan' => $upcomingTPS->kelurahans->kode_kelurahan,
                    'max_rw' => $upcomingTPS->kelurahans->max_rw,
                    'kecamatan' => $upcomingTPS->kelurahans->kecamatans,
                    'kabupaten' => $upcomingTPS->kelurahans->kabupaten_kotas,
                    'provinsi' => $upcomingTPS->kelurahans->provinsis,
                    'created_at' => $upcomingTPS->kelurahans->created_at,
                    'updated_at' => $upcomingTPS->kelurahans->updated_at
                ] : null,
                'tahun' => $upcomingTPS->tahun,
                'jumlah_tps' => $upcomingTPS->jumlah_tps,
                'created_at' => $upcomingTPS->created_at,
                'updated_at' => $upcomingTPS->updated_at
            ];

            $suaraKpuByPartai = $suaraKPU->groupBy('partai_id')->map(function ($items) {
                return [
                    'jumlah_suara' => $items->sum('jumlah_suara'),  // Sum jumlah_suara per partai
                    'partai_id' => $items->first()->partai_id       // Ambil partai_id dari grup
                ];
            });
            // dd($suaraKpuByPartai);
            $partaiWithMaxSuara = $suaraKpuByPartai->sortByDesc('jumlah_suara')->first();
            // dd($partaiWithMaxSuara);
            $suara_kpu_terbanyak = null;
            if ($partaiWithMaxSuara) {
                $partai = $suaraKPU->firstWhere('partai_id', $partaiWithMaxSuara['partai_id'])->partais ?? null;
                // dd($partai);
                if ($partai) {
                    $suara_kpu_terbanyak = [
                        'partai' => [
                            'id' => $partai->id,
                            'nama' => $partai->nama,
                            'color' => $partai->color,
                            'created_at' => $partai->created_at,
                            'updated_at' => $partai->updated_at
                        ],
                        'jumlah_suara' => $partaiWithMaxSuara['jumlah_suara']
                    ];
                }
            }
            // $status_aktivitas_kelurahan = StatusAktivitasHelper::DetermineStatusAktivitasKelurahan($list_rw);

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'Data suara KPU berhasil ditampilkan.',
                'data' => [
                    'chart' => $format_chart,
                    'table' => $format_suaraKPU,
                    'upcomingTPS' => $format_tps_mendatang,
                    'suara_kpu_terbanyak' => $suara_kpu_terbanyak,
                    'tahun' => $tahun
                ],
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Detail Map | - Error function indexSuaraKPU: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
