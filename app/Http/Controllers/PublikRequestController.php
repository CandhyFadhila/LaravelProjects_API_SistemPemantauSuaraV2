<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SuaraKPU;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\UpcomingTps;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\StatusAktivitas;
use App\Models\StatusAktivitasRw;
use App\Models\AktivitasPelaksana;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Cache;
use App\Helpers\StatusAktivitasHelper;
use App\Http\Resources\public\WithoutDataResource;

class PublikRequestController extends Controller
{
    protected $loggedInUser;
    protected $keyTags;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->loggedInUser = Auth::user();
            if ($this->loggedInUser) {
                $this->keyTags = $this->loggedInUser->id;
            } else {
                $this->keyTags = 'guest';
            }
            return $next($request);
        });
    }

    public function getAllDataRole()
    {
        if (!Gate::allows('view publikRequest')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $roles = Role::orderBy('created_at', 'desc')->get();
        if ($roles->isEmpty()) {
            return response()->json([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Data role tidak ditemukan.',
                'data' => []
            ], Response::HTTP_OK);
        }

        if ($this->loggedInUser->nama !== 'Super Admin') {
            $roles = $roles->filter(function ($role) {
                return $role->name !== 'Super Admin';
            });
        }

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all roles',
            'data' => $roles->values()
        ], Response::HTTP_OK);
    }

    public function getAllStatusAktivitas()
    {
        if (!Gate::allows('view publikRequest')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $status = StatusAktivitas::orderBy('created_at', 'desc')->get();
        if ($status->isEmpty()) {
            return response()->json([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Data status aktivitas tidak ditemukan.',
                'data' => []
            ], Response::HTTP_OK);
        }

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all status aktivitas',
            'data' => $status
        ], Response::HTTP_OK);
    }

    public function getAllStatusAktivitasRW()
    {
        if (!Gate::allows('view publikRequest')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $status_rw = Cache::rememberForever('public_get_all_status_aktivitas_rws_' . $this->keyTags, function () {
            return StatusAktivitasRw::orderBy('created_at', 'desc')->get();
        });
        if ($status_rw->isEmpty()) {
            return response()->json([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Data status aktivitas rw tidak ditemukan.',
                'data' => []
            ], Response::HTTP_OK);
        }

        $formattedData = $status_rw->map(function ($status_rw) {
            return [
                'id' => $status_rw->id,
                'kelurahan' => $status_rw->kelurahans ? [
                    'id' => $status_rw->kelurahans->id,
                    'nama_kelurahan' => $status_rw->kelurahans->nama_kelurahan,
                    'kode_kelurahan' => $status_rw->kelurahans->kode_kelurahan,
                    'max_rw' => $status_rw->kelurahans->max_rw,
                    'kecamatan' => $status_rw->kelurahans->kecamatans,
                    'kabupaten' => $status_rw->kelurahans->kabupaten_kotas,
                    'provinsi' => $status_rw->kelurahans->provinsis,
                    'created_at' => $status_rw->kelurahans->created_at,
                    'updated_at' => $status_rw->kelurahans->updated_at
                ] : null,
                'rw' => $status_rw->rw,
                'status_aktivitas' => $status_rw->aktivitas_status ? [
                    'id' => $status_rw->aktivitas_status->id,
                    'label' => $status_rw->aktivitas_status->label,
                    'created_at' => $status_rw->aktivitas_status->created_at,
                    'updated_at' => $status_rw->aktivitas_status->updated_at
                ] : null,
                'created_at' => $status_rw->created_at,
                'updated_at' => $status_rw->updated_at,
            ];
        });

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all suara kpu',
            'data' => $formattedData
        ]);
    }

    public function getAllDataUser()
    {
        if (!Gate::allows('view publikRequest')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $loggedInUser = $this->loggedInUser;
        $cacheKey = 'public_get_all_users_' . $this->keyTags;

        $users = Cache::rememberForever($cacheKey, function () use ($loggedInUser) {
            // Super Admin (role_id = 1), mendapatkan semua pengguna
            if ($loggedInUser->role_id == 1) {
                return User::orderBy('created_at', 'desc')
                    ->where('id', '!=', 1)
                    ->get();
            }
            // Penanggung Jawab (role_id = 2), hanya mendapatkan pengguna Penggerak (role_id = 3)
            elseif ($loggedInUser->role_id == 2) {
                return User::where('role_id', 3)
                    ->where('status_aktif', 2)
                    ->where('pj_pelaksana', $loggedInUser->id)
                    ->orderBy('created_at', 'desc')
                    ->get();
            } else {
                // Jika bukan Super Admin atau Penanggung Jawab
                return response()->json([
                    'status' => Response::HTTP_FORBIDDEN,
                    'message' => 'Anda tidak memiliki hak akses untuk melakukan proses ini.',
                ], Response::HTTP_FORBIDDEN);
            }
        });

        if ($users->isEmpty()) {
            return response()->json([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Data pengguna tidak ditemukan.',
                'data' => []
            ], Response::HTTP_OK);
        }

        $formattedData = $users->map(function ($user) {
            $role = $user->roles->first();

            $kelurahanIds = $user->kelurahan_id ?? null;
            $kelurahans = null;
            if (!empty($kelurahanIds)) {
                $kelurahans = Kelurahan::whereIn('id', $kelurahanIds)->get()->map(function ($kelurahan) {
                    return [
                        'id' => $kelurahan->id,
                        'nama_kelurahan' => $kelurahan->nama_kelurahan,
                        'kode_kelurahan' => $kelurahan->kode_kelurahan,
                        'max_rw' => $kelurahan->max_rw,
                        'provinsi_id' => $kelurahan->provinsis,
                        'kabupaten_id' => $kelurahan->kabupaten_kotas,
                        'kecamatan_id' => $kelurahan->kecamatans,
                        'created_at' => $kelurahan->created_at,
                        'updated_at' => $kelurahan->updated_at
                    ];
                });
            }

            $pjPelaksana = $user->pj_pelaksana ? User::find($user->pj_pelaksana) : null;
            $pjPelaksanaData = $pjPelaksana ? [
                'id' => $pjPelaksana->id,
                'nama' => $pjPelaksana->nama,
                'username' => $pjPelaksana->username,
                'jenis_kelamin' => $pjPelaksana->jenis_kelamin,
                'foto_profil' => $pjPelaksana->foto_profil ? env('STORAGE_SERVER_DOMAIN') . $pjPelaksana->foto_profil : null,
                'nik_ktp' => $pjPelaksana->nik_ktp,
                'no_hp' => $pjPelaksana->no_hp,
                'tgl_diangkat' => $pjPelaksana->tgl_diangkat,
                'role' => $pjPelaksana->roles->first() ? [
                    'id' => $pjPelaksana->roles->first()->id,
                    'name' => $pjPelaksana->roles->first()->name,
                    'deskripsi' => $pjPelaksana->roles->first()->deskripsi,
                    'created_at' => $pjPelaksana->roles->first()->created_at,
                    'updated_at' => $pjPelaksana->roles->first()->updated_at,
                ] : null,
                'kelurahan' => $pjPelaksana->kelurahan_id ? Kelurahan::whereIn('id', $pjPelaksana->kelurahan_id)->get()->map(function ($kelurahan) {
                    return [
                        'id' => $kelurahan->id,
                        'nama_kelurahan' => $kelurahan->nama_kelurahan,
                        'kode_kelurahan' => $kelurahan->kode_kelurahan,
                        'max_rw' => $kelurahan->max_rw,
                        'provinsi' => $kelurahan->provinsis,
                        'kabupaten' => $kelurahan->kabupaten_kotas,
                        'kecamatan' => $kelurahan->kecamatans,
                        'created_at' => $kelurahan->created_at,
                        'updated_at' => $kelurahan->updated_at
                    ];
                }) : null,
                'rw_pelaksana' => $pjPelaksana->rw_pelaksana ?? null,
                'status_aktif' => $pjPelaksana->status_users ? [
                    'id' => $pjPelaksana->status_users->id,
                    'label' => $pjPelaksana->status_users->label,
                    'created_at' => $pjPelaksana->status_users->created_at,
                    'updated_at' => $pjPelaksana->status_users->updated_at
                ] : null,
                'created_at' => $pjPelaksana->created_at,
                'updated_at' => $pjPelaksana->updated_at
            ] : null;

            return [
                'id' => $user->id,
                'nama' => $user->nama,
                'username' => $user->username,
                'jenis_kelamin' => $user->jenis_kelamin,
                'foto_profil' => $user->foto_profil ? env('STORAGE_SERVER_DOMAIN') . $user->foto_profil : null,
                'nik_ktp' => $user->nik_ktp,
                'no_hp' => $user->no_hp ?? null,
                'tgl_diangkat' => $user->tgl_diangkat,
                'role' => $role ? [
                    'id' => $role->id,
                    'name' => $role->name,
                    'deskripsi' => $role->deskripsi,
                    'created_at' => $role->created_at,
                    'updated_at' => $role->updated_at,
                ] : null,
                'kelurahan' => $kelurahans,
                'rw_pelaksana' => $user->rw_pelaksana ?? null,
                'pj_pelaksana' => $pjPelaksanaData,
                'status_aktif' => $user->status_users ? [
                    'id' => $user->status_users->id,
                    'label' => $user->status_users->label,
                    'created_at' => $user->status_users->created_at,
                    'updated_at' => $user->status_users->updated_at
                ] : null,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ];
        });

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all users',
            'data' => $formattedData
        ], Response::HTTP_OK);
    }

    public function getAllUserbyPenggerak()
    {
        $loggedInUser = $this->loggedInUser;
        if (!in_array($loggedInUser->role_id, [1, 2])) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $cacheKey = 'public_user_by_penggerak_' . $this->keyTags;

        $users = Cache::rememberForever($cacheKey, function () use ($loggedInUser) {
            // Super Admin (role_id = 1), mendapatkan semua pengguna
            if ($loggedInUser->role_id == 1) {
                return User::where('role_id', 3)
                    ->orderBy('created_at', 'desc')
                    ->get();
            }
            // Penanggung Jawab (role_id = 2), hanya mendapatkan pengguna Penggerak (role_id = 3)
            elseif ($loggedInUser->role_id == 2) {
                return User::where('role_id', 3)
                    ->where('status_aktif', 2)
                    ->where('pj_pelaksana', $loggedInUser->id)
                    ->orderBy('created_at', 'desc')
                    ->get();
            } else {
                // Jika bukan Super Admin atau Penanggung Jawab
                return response()->json([
                    'status' => Response::HTTP_FORBIDDEN,
                    'message' => 'Anda tidak memiliki hak akses untuk melakukan proses ini.',
                ], Response::HTTP_FORBIDDEN);
            }
        });

        if ($users->isEmpty()) {
            return response()->json([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Pengguna dengan role Penggerak tidak ditemukan.',
                'data' => []
            ], Response::HTTP_OK);
        }

        $formattedData = $users->map(function ($user) {
            $role = $user->roles->first();

            $kelurahanIds = $user->kelurahan_id ?? null;
            $kelurahans = null;
            if (!empty($kelurahanIds)) {
                $kelurahans = Kelurahan::whereIn('id', $kelurahanIds)->get()->map(function ($kelurahan) {
                    return [
                        'id' => $kelurahan->id,
                        'nama_kelurahan' => $kelurahan->nama_kelurahan,
                        'kode_kelurahan' => $kelurahan->kode_kelurahan,
                        'max_rw' => $kelurahan->max_rw,
                        'provinsi' => $kelurahan->provinsis,
                        'kabupaten' => $kelurahan->kabupaten_kotas,
                        'kecamatan' => $kelurahan->kecamatans,
                        'created_at' => $kelurahan->created_at,
                        'updated_at' => $kelurahan->updated_at
                    ];
                });
            }

            $pjPelaksana = $user->pj_pelaksana ? User::find($user->pj_pelaksana) : null;
            $pjPelaksanaData = $pjPelaksana ? [
                'id' => $pjPelaksana->id,
                'nama' => $pjPelaksana->nama,
                'username' => $pjPelaksana->username,
                'jenis_kelamin' => $pjPelaksana->jenis_kelamin,
                'foto_profil' => $pjPelaksana->foto_profil ? env('STORAGE_SERVER_DOMAIN') . $pjPelaksana->foto_profil : null,
                'nik_ktp' => $pjPelaksana->nik_ktp,
                'no_hp' => $pjPelaksana->no_hp,
                'tgl_diangkat' => $pjPelaksana->tgl_diangkat,
                'role' => $pjPelaksana->roles->first() ? [
                    'id' => $pjPelaksana->roles->first()->id,
                    'name' => $pjPelaksana->roles->first()->name,
                    'deskripsi' => $pjPelaksana->roles->first()->deskripsi,
                    'created_at' => $pjPelaksana->roles->first()->created_at,
                    'updated_at' => $pjPelaksana->roles->first()->updated_at,
                ] : null,
                'kelurahan' => $pjPelaksana->kelurahan_id ? Kelurahan::whereIn('id', $pjPelaksana->kelurahan_id)->get()->map(function ($kelurahan) {
                    return [
                        'id' => $kelurahan->id,
                        'nama_kelurahan' => $kelurahan->nama_kelurahan,
                        'kode_kelurahan' => $kelurahan->kode_kelurahan,
                        'max_rw' => $kelurahan->max_rw,
                        'provinsi' => $kelurahan->provinsis,
                        'kabupaten' => $kelurahan->kabupaten_kotas,
                        'kecamatan' => $kelurahan->kecamatans,
                        'created_at' => $kelurahan->created_at,
                        'updated_at' => $kelurahan->updated_at
                    ];
                }) : null,
                'rw_pelaksana' => $pjPelaksana->rw_pelaksana ?? null,
                'status_aktif' => $pjPelaksana->status_users ? [
                    'id' => $pjPelaksana->status_users->id,
                    'label' => $pjPelaksana->status_users->label,
                    'created_at' => $pjPelaksana->status_users->created_at,
                    'updated_at' => $pjPelaksana->status_users->updated_at
                ] : null,
                'created_at' => $pjPelaksana->created_at,
                'updated_at' => $pjPelaksana->updated_at
            ] : null;

            return [
                'id' => $user->id,
                'nama' => $user->nama,
                'username' => $user->username,
                'jenis_kelamin' => $user->jenis_kelamin,
                'foto_profil' => $user->foto_profil ? env('STORAGE_SERVER_DOMAIN') . $user->foto_profil : null,
                'nik_ktp' => $user->nik_ktp,
                'no_hp' => $user->no_hp ?? null,
                'tgl_diangkat' => $user->tgl_diangkat,
                'role' => $role ? [
                    'id' => $role->id,
                    'name' => $role->name,
                    'deskripsi' => $role->deskripsi,
                    'created_at' => $role->created_at,
                    'updated_at' => $role->updated_at,
                ] : null,
                'kelurahan' => $kelurahans,
                'rw_pelaksana' => $user->rw_pelaksana ?? null,
                'pj_pelaksana' => $pjPelaksanaData,
                'status_aktif' => $user->status_users ? [
                    'id' => $user->status_users->id,
                    'label' => $user->status_users->label,
                    'created_at' => $user->status_users->created_at,
                    'updated_at' => $user->status_users->updated_at
                ] : null,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ];
        });

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all users',
            'data' => $formattedData
        ], Response::HTTP_OK);
    }

    public function getProfileUser($userId)
    {
        if (!Gate::allows('view publikRequest')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $users = User::where('id', $userId)->where('id', '!=', 1)->get();
        if ($users->isEmpty()) {
            return response()->json([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Data pengguna tidak ditemukan.',
                'data' => null
            ], Response::HTTP_OK);
        }

        $formattedData = $users->map(function ($user) {
            $role = $user->roles->first();

            $pjPelaksana = $user->pj_pelaksana ? User::find($user->pj_pelaksana) : null;
            $pjPelaksanaData = $pjPelaksana ? [
                'id' => $pjPelaksana->id,
                'nama' => $pjPelaksana->nama,
                'username' => $pjPelaksana->username,
                'jenis_kelamin' => $pjPelaksana->jenis_kelamin,
                'foto_profil' => $pjPelaksana->foto_profil ? env('STORAGE_SERVER_DOMAIN') . $pjPelaksana->foto_profil : null,
                'nik_ktp' => $pjPelaksana->nik_ktp,
                'no_hp' => $pjPelaksana->no_hp,
                'tgl_diangkat' => $pjPelaksana->tgl_diangkat,
                'role' => $pjPelaksana->roles->first() ? [
                    'id' => $pjPelaksana->roles->first()->id,
                    'name' => $pjPelaksana->roles->first()->name,
                    'deskripsi' => $pjPelaksana->roles->first()->deskripsi,
                    'created_at' => $pjPelaksana->roles->first()->created_at,
                    'updated_at' => $pjPelaksana->roles->first()->updated_at,
                ] : null,
                'status_aktif' => $pjPelaksana->status_users ? [
                    'id' => $pjPelaksana->status_users->id,
                    'label' => $pjPelaksana->status_users->label,
                    'created_at' => $pjPelaksana->status_users->created_at,
                    'updated_at' => $pjPelaksana->status_users->updated_at
                ] : null,
                'created_at' => $pjPelaksana->created_at,
                'updated_at' => $pjPelaksana->updated_at
            ] : null;

            return [
                'id' => $user->id,
                'nama' => $user->nama,
                'username' => $user->username,
                'jenis_kelamin' => $user->jenis_kelamin,
                'foto_profil' => $user->foto_profil ? env('STORAGE_SERVER_DOMAIN') . $user->foto_profil : null,
                'nik_ktp' => $user->nik_ktp,
                'no_hp' => $user->no_hp ?? null,
                'tgl_diangkat' => $user->tgl_diangkat,
                'role' => $role ? [
                    'id' => $role->id,
                    'name' => $role->name,
                    'deskripsi' => $role->deskripsi,
                    'created_at' => $role->created_at,
                    'updated_at' => $role->updated_at,
                ] : null,
                'pj_pelaksana' => $pjPelaksanaData,
                'status_aktif' => $user->status_users ? [
                    'id' => $user->status_users->id,
                    'label' => $user->status_users->label,
                    'created_at' => $user->status_users->created_at,
                    'updated_at' => $user->status_users->updated_at
                ] : null,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ];
        });

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all users',
            'data' => $formattedData
        ], Response::HTTP_OK);
    }

    public function getAllDataKecamatan()
    {
        if (!Gate::allows('view publikRequest')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $kecamatan = Kecamatan::all();
        if ($kecamatan->isEmpty()) {
            return response()->json([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Data kecamatan tidak ditemukan.',
                'data' => []
            ], Response::HTTP_OK);
        }

        $formattedData = $kecamatan->map(function ($kecamatan) {
            return [
                'id' => $kecamatan->id,
                'nama_kecamatan' => $kecamatan->nama_kecamatan,
                'kode_kecamatan' => $kecamatan->kode_kecamatan,
                'provinsi' => $kecamatan->provinsis,
                'kabupaten' => $kecamatan->kabupaten_kotas,
                'created_at' => $kecamatan->created_at,
                'updated_at' => $kecamatan->updated_at
            ];
        });

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all kecamatans',
            'data' => $formattedData
        ]);
    }

    public function getAllDataKelurahan()
    {
        if (!Gate::allows('view publikRequest')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $kelurahan = Cache::rememberForever('public_get_all_kelurahan_' . $this->keyTags, function () {
            return Kelurahan::all();
        });
        if ($kelurahan->isEmpty()) {
            return response()->json([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Data kelurahan tidak ditemukan.',
                'data' => []
            ], Response::HTTP_OK);
        }

        $formattedData = $kelurahan->map(function ($kelurahan) {
            return [
                'id' => $kelurahan->id,
                'nama_kelurahan' => $kelurahan->nama_kelurahan,
                'kode_kelurahan' => $kelurahan->kode_kelurahan,
                'max_rw' => $kelurahan->max_rw,
                'kecamatan' => $kelurahan->kecamatans,
                'kabupaten' => $kelurahan->kabupaten_kotas,
                'provinsi' => $kelurahan->provinsis,
                'created_at' => $kelurahan->created_at,
                'updated_at' => $kelurahan->updated_at
            ];
        });

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all kelurahans',
            'data' => $formattedData
        ]);
    }

    public function getKelurahanUserId($userId)
    {
        $user = User::find($userId);
        if (!$user) {
            return response()->json([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Pengguna tidak ditemukan.',
                'data' => []
            ], Response::HTTP_OK);
        }

        if (empty($user->kelurahan_id)) {
            return response()->json([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Tidak ada kelurahan yang terkait dengan pengguna ini.',
                'data' => []
            ], Response::HTTP_OK);
        }

        $kelurahans = Kelurahan::whereIn('id', $user->kelurahan_id)->get();
        if ($kelurahans->isEmpty()) {
            return response()->json([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Kelurahan tidak ditemukan.',
                'data' => []
            ], Response::HTTP_OK);
        }

        $formattedKelurahans = $kelurahans->map(function ($kelurahan) {
            return [
                'id' => $kelurahan->id,
                'nama_kelurahan' => $kelurahan->nama_kelurahan,
                'kode_kelurahan' => $kelurahan->kode_kelurahan,
                'max_rw' => $kelurahan->max_rw,
                'provinsi_id' => $kelurahan->provinsis,
                'kabupaten_id' => $kelurahan->kabupaten_kotas,
                'kecamatan_id' => $kelurahan->kecamatans,
                'created_at' => $kelurahan->created_at,
                'updated_at' => $kelurahan->updated_at
            ];
        });

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Data kelurahan dari pengguna '{$user->nama}' berhasil diambil.",
            'data' => $formattedKelurahans
        ], Response::HTTP_OK);
    }

    public function getAllDataAktivitas()
    {
        if (!Gate::allows('view publikRequest')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $loggedInUser = $this->loggedInUser;
        $cacheKey = 'public_get_all_aktivitas_' . $this->keyTags;

        $aktivitas = Cache::rememberForever($cacheKey, function () use ($loggedInUser) {
            if ($loggedInUser->role_id == 1) {
                return AktivitasPelaksana::orderBy('created_at', 'desc')->get();
            } elseif ($loggedInUser->role_id == 2) {
                return AktivitasPelaksana::where(function ($query) use ($loggedInUser) {
                    $query->whereHas('pelaksana_users', function ($subQuery) use ($loggedInUser) {
                        $subQuery->where('role_id', 3)->where('pj_pelaksana', $loggedInUser->id);
                    })->orWhere('pelaksana', $loggedInUser->id);
                })->orderBy('created_at', 'desc')->get();
            } elseif ($loggedInUser->role_id == 3) {
                return AktivitasPelaksana::where('pelaksana', $loggedInUser->id)->orderBy('created_at', 'desc')->get();
            } else {
                return response()->json([
                    'status' => Response::HTTP_FORBIDDEN,
                    'message' => 'Anda tidak memiliki hak akses untuk melihat aktivitas ini.'
                ], Response::HTTP_FORBIDDEN);
            }
        });

        if ($aktivitas->isEmpty()) {
            return response()->json([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Data aktivitas tidak ditemukan.',
                'data' => []
            ], Response::HTTP_OK);
        }

        $formattedData = $aktivitas->map(function ($aktivitas) {
            return [
                'id' => $aktivitas->id,
                'pelaksana' => $aktivitas->pelaksana_users ? [
                    'id' => $aktivitas->pelaksana_users->id,
                    'nama' => $aktivitas->pelaksana_users->nama,
                    'username' => $aktivitas->pelaksana_users->username,
                    'nik_ktp' => $aktivitas->pelaksana_users->nik_ktp,
                    'foto_profil' =>  $aktivitas->pelaksana_users->foto_profil ? env('STORAGE_SERVER_DOMAIN') . $aktivitas->pelaksana_users->foto_profil : null,
                    'tgl_diangkat' => $aktivitas->pelaksana_users->tgl_diangkat,
                    'jenis_kelamin' => $aktivitas->pelaksana_users->jenis_kelamin,
                    'role_id' => $aktivitas->pelaksana_users->role_id,
                    'status_aktif' => $aktivitas->pelaksana_users->status_aktif,
                    'created_at' => $aktivitas->pelaksana_users->created_at,
                    'updated_at' => $aktivitas->pelaksana_users->updated_at
                ] : null,
                'status_aktivitas' => $aktivitas->status ? [
                    'id' => $aktivitas->status->id,
                    'label' => $aktivitas->status->label,
                    'created_at' => $aktivitas->status->created_at,
                    'updated_at' => $aktivitas->status->updated_at
                ] : null,
                'deskripsi' => $aktivitas->deskripsi,
                'tgl_mulai' => $aktivitas->tgl_mulai,
                'tgl_selesai' => $aktivitas->tgl_selesai,
                'tempat_aktivitas' => $aktivitas->tempat_aktivitas,
                'foto_aktivitas' => $aktivitas->foto_aktivitas ? env('STORAGE_SERVER_DOMAIN') . $aktivitas->foto_aktivitas : null,
                'kelurahan' => $aktivitas->kelurahans ? [
                    'id' => $aktivitas->kelurahans->id,
                    'nama_kelurahan' => $aktivitas->kelurahans->nama_kelurahan,
                    'kode_kelurahan' => $aktivitas->kelurahans->kode_kelurahan,
                    'max_rw' => $aktivitas->kelurahans->max_rw,
                    'kecamatan' => $aktivitas->kelurahans->kecamatans,
                    'kabupaten' => $aktivitas->kelurahans->kabupaten_kotas,
                    'provinsi' => $aktivitas->kelurahans->provinsis,
                    'created_at' => $aktivitas->kelurahans->created_at,
                    'updated_at' => $aktivitas->kelurahans->updated_at
                ] : null,
                'potensi_suara' => $aktivitas->potensi_suara,
                'status_aktivitas_rw' => $aktivitas->aktivitas_rws ? [
                    'id' => $aktivitas->aktivitas_rws->id,
                    'kelurahan' => $aktivitas->aktivitas_rws->kelurahans ? [
                        'id' => $aktivitas->aktivitas_rws->kelurahans->id,
                        'nama_kelurahan' => $aktivitas->aktivitas_rws->kelurahans->nama_kelurahan,
                        'kode_kelurahan' => $aktivitas->aktivitas_rws->kelurahans->kode_kelurahan,
                        'max_rw' => $aktivitas->aktivitas_rws->kelurahans->max_rw,
                        'kecamatan' => $aktivitas->aktivitas_rws->kelurahans->kecamatans,
                        'kabupaten' => $aktivitas->aktivitas_rws->kelurahans->kabupaten_kotas,
                        'provinsi' => $aktivitas->aktivitas_rws->kelurahans->provinsis,
                        'created_at' => $aktivitas->aktivitas_rws->kelurahans->created_at,
                        'updated_at' => $aktivitas->aktivitas_rws->kelurahans->updated_at
                    ] : null,
                    'rw' => $aktivitas->aktivitas_rws->rw,
                    'status_aktivitas' => $aktivitas->status ? [
                        'id' => $aktivitas->status->id,
                        'label' => $aktivitas->status->label,
                        'created_at' => $aktivitas->status->created_at,
                        'updated_at' => $aktivitas->status->updated_at
                    ] : null,
                ] : null,
                'created_at' => $aktivitas->created_at,
                'updated_at' => $aktivitas->updated_at,
            ];
        });

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all aktivitas',
            'data' => $formattedData
        ]);
    }

    public function getAllDataSuaraKPU()
    {
        if (!Gate::allows('view publikRequest')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $suara_kpu = Cache::rememberForever('public_get_all_suara_kpu_' . $this->keyTags, function () {
            return SuaraKPU::orderBy('created_at', 'desc')->get();
        });
        if ($suara_kpu->isEmpty()) {
            return response()->json([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Data suara kpu tidak ditemukan.',
                'data' => []
            ], Response::HTTP_OK);
        }

        $formattedData = $suara_kpu->map(function ($suara_kpu) {
            return [
                'id' => $suara_kpu->id,
                'partai' => $suara_kpu->partais ? [
                    'id' => $suara_kpu->partais->id,
                    'nama' => $suara_kpu->partais->nama,
                    'color' => $suara_kpu->partais->color ?? null
                ] : null,
                'kelurahan' => $suara_kpu->kelurahans ? [
                    'id' => $suara_kpu->kelurahans->id,
                    'nama_kelurahan' => $suara_kpu->kelurahans->nama_kelurahan,
                    'kode_kelurahan' => $suara_kpu->kelurahans->kode_kelurahan,
                    'max_rw' => $suara_kpu->kelurahans->max_rw,
                    'kecamatan' => $suara_kpu->kelurahans->kecamatans,
                    'kabupaten' => $suara_kpu->kelurahans->kabupaten_kotas,
                    'provinsi' => $suara_kpu->kelurahans->provinsis,
                    'created_at' => $suara_kpu->kelurahans->created_at,
                    'updated_at' => $suara_kpu->kelurahans->updated_at
                ] : null,
                'tahun' => $suara_kpu->tahun,
                'cakupan_wilayah' => $suara_kpu->cakupan_wilayah,
                'kategori_suara' => $suara_kpu->kategori_suaras,
                'tps' => $suara_kpu->tps,
                'jumlah_suara' => $suara_kpu->jumlah_suara,
                'dpt_laki' => $suara_kpu->dpt_laki,
                'dpt_perempuan' => $suara_kpu->dpt_perempuan,
                'jumlah_dpt' => $suara_kpu->jumlah_dpt,
                'suara_caleg' => $suara_kpu->suara_caleg ?? 'N/A',
                'suara_partai' => $suara_kpu->suara_partai ?? 'N/A',
                'created_at' => $suara_kpu->created_at,
                'updated_at' => $suara_kpu->updated_at,
            ];
        });

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all suara kpu',
            'data' => $formattedData
        ]);
    }

    public function getAllDataUpcomingTPS()
    {
        if (!Gate::allows('view upcomingTPS')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $prakiraan_tps = Cache::rememberForever('public_get_all_data_upcoming_tps_' . $this->keyTags, function () {
            return UpcomingTps::orderBy('created_at', 'desc')->get();
        });
        if ($prakiraan_tps->isEmpty()) {
            return response()->json([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Data prakiraan tps tidak ditemukan.',
                'data' => []
            ], Response::HTTP_OK);
        }

        $formattedData = $prakiraan_tps->map(function ($prakiraan_tps) {
            return [
                'id' => $prakiraan_tps->id,
                'kelurahan' => $prakiraan_tps->kelurahans ? [
                    'id' => $prakiraan_tps->kelurahans->id,
                    'nama_kelurahan' => $prakiraan_tps->kelurahans->nama_kelurahan,
                    'kode_kelurahan' => $prakiraan_tps->kelurahans->kode_kelurahan,
                    'max_rw' => $prakiraan_tps->kelurahans->max_rw,
                    'kecamatan' => $prakiraan_tps->kelurahans->kecamatans,
                    'kabupaten' => $prakiraan_tps->kelurahans->kabupaten_kotas,
                    'provinsi' => $prakiraan_tps->kelurahans->provinsis,
                    'created_at' => $prakiraan_tps->kelurahans->created_at,
                    'updated_at' => $prakiraan_tps->kelurahans->updated_at
                ] : null,
                'tahun' => $prakiraan_tps->tahun,
                'jumlah_tps' => $prakiraan_tps->jumlah_tps,
                'created_at' => $prakiraan_tps->created_at,
                'updated_at' => $prakiraan_tps->updated_at,
            ];
        });

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all data prakiraan tps',
            'data' => $formattedData
        ]);
    }

    public function getDataMapsKelurahan(Request $request)
    {
        if (!Gate::allows('view publikRequest')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $loggedInUser = $this->loggedInUser;

        $kategori_suara = $request->input('kategori_suara', []);
        $tahun = $request->input('tahun', []);
        if (empty($kategori_suara) || empty($tahun)) {
            return response()->json([
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => 'Kategori suara dan tahun diperlukan.',
                'data' => null
            ], Response::HTTP_BAD_REQUEST);
        }

        $cacheKey = 'public_get_all_kelurahan_' . $this->keyTags;
        $kelurahan = Cache::rememberForever($cacheKey, function () use ($loggedInUser) {
            if ($loggedInUser->role_id == 1) {
                return Kelurahan::all();
            }
            if ($loggedInUser->kelurahan_id && !empty($loggedInUser->kelurahan_id)) {
                return Kelurahan::whereIn('id', $loggedInUser->kelurahan_id)->get();
            }
        });
        if ($kelurahan->isEmpty()) {
            return response()->json([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Data kelurahan tidak ditemukan.',
                'data' => []
            ], Response::HTTP_OK);
        }

        $statusAktivitasRw = Cache::rememberForever('public_get_all_status_aktivitas_rws_kelurahan_' . $this->keyTags, function () use ($kelurahan) {
            return StatusAktivitasRw::whereIn('kelurahan_id', $kelurahan->pluck('id'))
                ->with('aktivitas_status')
                ->get();
        });
        $formattedData = $kelurahan->map(function ($kelurahan) use ($statusAktivitasRw, $kategori_suara, $tahun) {
            $maxRw = $kelurahan->max_rw;
            $list_rw = array_fill(0, $maxRw, null);

            foreach ($statusAktivitasRw as $status) {
                if ($status->kelurahan_id == $kelurahan->id && $status->rw <= $maxRw) {
                    $list_rw[$status->rw - 1] = $status->status_aktivitas;
                }
            }
            $status_aktivitas_kelurahan = StatusAktivitasHelper::DetermineStatusAktivitasKelurahan($list_rw);

            $cacheKey = 'public_suara_kpu_' . $this->keyTags . '_' . $kelurahan->kode_kelurahan;
            $suara_kpu = Cache::rememberForever($cacheKey, function () use ($kelurahan, $tahun, $kategori_suara) {
                return SuaraKPU::where('kelurahan_id', $kelurahan->id)
                    ->whereIn('tahun', $tahun)
                    ->whereIn('kategori_suara_id', $kategori_suara)
                    ->get();
            });
            $suaraKpuByPartai = $suara_kpu->where('kelurahan_id', $kelurahan->id)->groupBy('partai_id')->map(function ($items) {
                return [
                    'jumlah_suara' => $items->sum('jumlah_suara'),  // Sum jumlah_suara per partai
                    'partai_id' => $items->first()->partai_id       // Ambil partai_id dari grup
                ];
            });
            // dd($suaraKpuByPartai);

            // Urutkan partai berdasarkan jumlah suara terbanyak
            $partaiWithMaxSuara = $suaraKpuByPartai->sortByDesc('jumlah_suara')->first();
            // dd($partaiWithMaxSuara);

            $suara_kpu_terbanyak = null;
            if ($partaiWithMaxSuara) {
                // Ambil nama partai dari data suara kpu pertama yang sesuai dengan partai_id terbanyak
                $partai = $suara_kpu->firstWhere('partai_id', $partaiWithMaxSuara['partai_id'])->partais ?? null;
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

            return [
                'id' => $kelurahan->id,
                'nama_kelurahan' => $kelurahan->nama_kelurahan,
                'kode_kelurahan' => $kelurahan->kode_kelurahan,
                'max_rw' => $kelurahan->max_rw,
                'kecamatan' => $kelurahan->kecamatans,
                'kabupaten' => $kelurahan->kabupaten_kotas,
                'provinsi' => $kelurahan->provinsis,
                // 'list_rw' => $list_rw, // buat debug
                'status_aktivitas_kelurahan' => $status_aktivitas_kelurahan,
                'suara_kpu_terbanyak' => $suara_kpu_terbanyak,
                'created_at' => $kelurahan->created_at,
                'updated_at' => $kelurahan->updated_at
            ];
        });

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all kelurahans with kpus',
            'data' => $formattedData
        ]);
    }
}
