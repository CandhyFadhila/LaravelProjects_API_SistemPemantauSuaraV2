<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\User;
use App\Models\Kelurahan;
use Illuminate\Http\Request;
use App\Helpers\RandomHelper;
use Illuminate\Http\Response;
use App\Helpers\FileUploadHelper;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Exports\Pengguna\PenggunaExport;
use App\Helpers\Filters\UserFilterHelper;
use App\Http\Requests\UpdateUserPasswordRequest;
use App\Http\Resources\public\WithoutDataResource;
use App\Http\Requests\Pengguna\StorePenggunaRequest;
use App\Http\Requests\Pengguna\UpdatePenggunaRequest;

class PenggunaController extends Controller
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

    public function index(Request $request)
    {
        if (!Gate::allows('view pengguna')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $loggedInUser = $this->loggedInUser;
        $limit = $request->input('limit', 10);

        if ($loggedInUser->role_id == 1) {
            $query = User::query()->where('id', '!=', 1)->orderBy('created_at', 'desc');
            $cacheKey = 'user_role_1_' . $this->keyTags;
        } elseif ($loggedInUser->role_id == 2) {
            $query = User::query()
                ->where('role_id', 3)
                ->where('status_aktif', 2)
                ->where('pj_pelaksana', $loggedInUser->id)
                ->orderBy('created_at', 'desc');
            $cacheKey = 'user_role_2_' . $this->keyTags;
        } else {
            return response()->json([
                'status' => Response::HTTP_FORBIDDEN,
                'message' => 'Anda tidak memiliki hak akses untuk melakukan proses ini.',
            ], Response::HTTP_FORBIDDEN);
        }

        $filters = $request->all();
        $query = UserFilterHelper::applyFiltersUser($query, $filters);

        $users = Cache::rememberForever($cacheKey, function () use ($query) {
            return $query->get();
        });

        if ($limit == 0) {
            $users = $query->get();
            $paginationData = null;
        } else {
            $limit = is_numeric($limit) ? (int)$limit : 10;
            $users = $query->paginate($limit);

            $paginationData = [
                'links' => [
                    'first' => $users->url(1),
                    'last' => $users->url($users->lastPage()),
                    'prev' => $users->previousPageUrl(),
                    'next' => $users->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                ]
            ];
        }

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

            if ($kelurahanIds !== null) {
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
                'updated_at' => $user->updated_at
            ];
        });

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Data pengguna berhasil ditampilkan.',
            'data' => $formattedData,
            'pagination' => $paginationData
        ], Response::HTTP_OK);
    }

    public function store(StorePenggunaRequest $request)
    {
        if (!Gate::allows('create pengguna')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $loggedInUser = $this->loggedInUser;

        // Validasi role_id = 1 hanya bisa membuat role_id = 2
        if ($loggedInUser->role_id == 1 && $data['role_id'] != 2) {
            return response()->json([
                'status' => Response::HTTP_FORBIDDEN,
                'message' => 'Pengguna dengan role Super Admin hanya bisa membuat pengguna dengan peran Penanggung Jawab.'
            ], Response::HTTP_FORBIDDEN);
        }

        // Validasi role_id = 2 hanya bisa membuat role_id = 3
        if ($loggedInUser->role_id == 2 && $data['role_id'] != 3) {
            return response()->json([
                'status' => Response::HTTP_FORBIDDEN,
                'message' => 'Pengguna dengan role Penanggung Jawab hanya bisa membuat pengguna dengan peran Penggerak.'
            ], Response::HTTP_FORBIDDEN);
        }

        // Validasi: Jika bukan role_id = 2, pengguna tidak diperbolehkan mengisi rw_pelaksana
        if ($loggedInUser->role_id != 2 && isset($data['rw_pelaksana'])) {
            return response()->json([
                'status' => Response::HTTP_FORBIDDEN,
                'message' => 'Hanya pengguna dengan role Penanggung Jawab yang diperbolehkan menginputkan RW Pelaksana.'
            ], Response::HTTP_FORBIDDEN);
        }

        $username = $data['username'] ?? RandomHelper::generateUsername($data['nama']);
        $existingUser = User::where('username', $username)->first();
        if ($existingUser) {
            $username .= rand(100, 999); // Tambahkan angka untuk membuat username unik
        }

        $password = $data['password'] ?? RandomHelper::generatePasswordBasic();

        $fotoProfilPath = null;
        if ($request->hasFile('foto_profil')) {
            $fotoProfilPath = FileUploadHelper::storePhoto($request->file('foto_profil'), 'profiles');
        }

        $kelurahanIds = [];
        if (isset($data['kelurahan_id'])) {
            foreach ($data['kelurahan_id'] as $kode_kelurahan) {
                $kelurahan = Kelurahan::where('kode_kelurahan', $kode_kelurahan)->first();
                if ($kelurahan) {
                    $kelurahanIds[] = $kelurahan->id;
                } else {
                    return response()->json([
                        'status' => Response::HTTP_BAD_REQUEST,
                        'message' => "Kelurahan dengan kode '$kode_kelurahan' tidak ditemukan."
                    ], Response::HTTP_BAD_REQUEST);
                }
            }
        }

        // if ($loggedInUser->role_id == 2 && $data['role_id'] == 3) {
        //     $kelurahanIds = $loggedInUser->kelurahan_id;
        // }

        $rwPelaksana = null;
        if ($loggedInUser->role_id == 2 && $data['role_id'] == 3) {
            $rwPelaksana = $data['rw_pelaksana'] ?? null;
        }

        $pjPelaksana = null;
        if ($loggedInUser->role_id == 2 && $data['role_id'] == 3) {
            $pjPelaksana = $loggedInUser->id;
        }

        $user = [
            'nama' => $data['nama'],
            'username' => $username,
            'jenis_kelamin' => $data['jenis_kelamin'],
            'tgl_diangkat' => $data['tgl_diangkat'],
            'nik_ktp' => $data['nik_ktp'],
            'foto_profil' => $fotoProfilPath,
            'no_hp' => $data['no_hp'] ?? null,
            'role_id' => $data['role_id'],
            'kelurahan_id' => $kelurahanIds,
            'rw_pelaksana' => $rwPelaksana,
            'pj_pelaksana' => $pjPelaksana,
            'password' => Hash::make($password),
        ];
        $createUser = User::create($user);
        // Ambil nama role berdasarkan role_id
        $role = Role::find($data['role_id']);
        if ($role) {
            // Atur role menggunakan nama role asli
            $createUser->syncRoles([$role->name]);
        } else {
            return response()->json([
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => 'Role tidak ditemukan.'
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'status' => Response::HTTP_CREATED,
            'message' => "Pengguna baru '{$createUser->nama}' berhasil ditambahkan."
        ], Response::HTTP_CREATED);
    }

    public function show($id)
    {
        if (!Gate::allows('view pengguna')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $user = User::where('id', '!=', 1)->find($id);
        if (!$user) {
            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'Pengguna tidak ditemukan.'
            ], Response::HTTP_NOT_FOUND);
        }

        $role = $user->roles->first();

        $kelurahanIds = $user->kelurahan_id ?? null;
        $kelurahanData = null;
        if (!empty($kelurahanIds)) {
            $kelurahanData = Kelurahan::whereIn('id', $kelurahanIds)->get()->map(function ($kelurahan) {
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

        $formattedData = [
            'id' => $user->id,
            'nama' => $user->nama,
            'username' => $user->username,
            'jenis_kelamin' => $user->jenis_kelamin,
            'foto_profil' => $user->foto_profil ? env('STORAGE_SERVER_DOMAIN') . $user->foto_profil : null,
            'nik_ktp' => $user->nik_ktp,
            'no_hp' => $user->no_hp ?? null,
            'tgl_diangkat' => $user->tgl_diangkat,
            'status_aktif' => $user->status_aktif,
            'role' => $role ? [
                'id' => $role->id,
                'name' => $role->name,
                'deskripsi' => $role->deskripsi,
                'created_at' => $role->created_at,
                'updated_at' => $role->updated_at,
            ] : null,
            'kelurahan' => $kelurahanData,
            'rw_pelaksana' => $user->rw_pelaksana ?? null,
            'pj_pelaksana' => $pjPelaksanaData,
            'status_aktif' => $user->status_users ? [
                'id' => $user->status_users->id,
                'label' => $user->status_users->label,
                'created_at' => $user->status_users->created_at,
                'updated_at' => $user->status_users->updated_at
            ] : null,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at
        ];

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Detail pengguna '{$user->nama}' berhasil ditampilkan.",
            'data' => $formattedData
        ], Response::HTTP_OK);
    }

    public function update(UpdatePenggunaRequest $request, $id)
    {
        if (!Gate::allows('edit pengguna')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $loggedInUser = $this->loggedInUser;
        $user = User::findOrFail($id);

        if ($loggedInUser->role_id == 1 && $user->role_id != 2) {
            return response()->json([
                'status' => Response::HTTP_FORBIDDEN,
                'message' => 'Super Admin hanya bisa mengedit pengguna dengan peran Penanggung Jawab.'
            ], Response::HTTP_FORBIDDEN);
        }

        if ($loggedInUser->role_id == 2 && $user->role_id != 3) {
            return response()->json([
                'status' => Response::HTTP_FORBIDDEN,
                'message' => 'Penanggung Jawab hanya bisa mengedit pengguna dengan peran Penggerak.'
            ], Response::HTTP_FORBIDDEN);
        }

        $validatedData = $request->validated();

        if ($request->hasFile('foto_profil')) {
            // Hapus foto lama jika ada
            if ($user->foto_profil && Storage::disk('public')->exists($user->foto_profil)) {
                FileUploadHelper::deletePhoto($user->foto_profil);
            }

            // Upload foto baru
            $fotoProfilPath = FileUploadHelper::storePhoto($request->file('foto_profil'), 'profiles');
            $user->foto_profil = $fotoProfilPath;
        }

        $user->nama = $validatedData['nama'] ?? $user->nama;
        $user->jenis_kelamin = $validatedData['jenis_kelamin'] ?? $user->jenis_kelamin;
        $user->nik_ktp = $validatedData['nik_ktp'] ?? $user->nik_ktp;
        $user->no_hp = $validatedData['no_hp'] ?? $user->no_hp;
        $user->role_id = $validatedData['role_id'] ?? $user->role_id;

        // Jika ada role_id, update role berdasarkan nama role
        if (isset($validatedData['role_id'])) {
            $role = Role::find($validatedData['role_id']);
            if ($role) {
                $user->syncRoles([$role->name]);
            } else {
                return response()->json([
                    'status' => Response::HTTP_BAD_REQUEST,
                    'message' => 'Role tidak ditemukan.'
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        // Konversi kode_kelurahan menjadi ID kelurahan
        if (isset($validatedData['kelurahan_id']) && is_array($validatedData['kelurahan_id'])) {
            $kelurahanIds = Kelurahan::whereIn('kode_kelurahan', $validatedData['kelurahan_id'])->pluck('id');
            if ($kelurahanIds->isEmpty()) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Kelurahan tidak ditemukan.'
                ], Response::HTTP_NOT_FOUND);
            }
            $user->kelurahan_id = $kelurahanIds;
        }

        if (isset($validatedData['rw_pelaksana']) && is_array($validatedData['rw_pelaksana'])) {
            $user->rw_pelaksana = $validatedData['rw_pelaksana'];
        }

        $user->save();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Data pengguna '{$user->nama}' berhasil diperbarui."
        ], Response::HTTP_OK);
    }

    public function toggleStatusUser($id)
    {
        if (!Gate::allows('aktifkan pengguna')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $user = User::where('id', '!=', 1)->find($id);
        if (!$user) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Pengguna tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        // Logika toggle status pengguna
        if ($user->status_aktif === 1) {
            $user->status_aktif = 2; // Aktifkan user
            $message = "Pengguna '{$user->nama}' berhasil diaktifkan.";
        } elseif ($user->status_aktif === 2) {
            $user->status_aktif = 3; // Nonaktifkan user
            $message = "Pengguna '{$user->nama}' berhasil dinonaktifkan.";
        } elseif ($user->status_aktif === 3) {
            $user->status_aktif = 2; // Aktifkan kembali
            $message = "Pengguna '{$user->nama}' berhasil diaktifkan kembali.";
        }
        $user->save();

        Cache::forget('public_get_all_users_' . $this->keyTags);
        Cache::forget('user_role_1_' . $this->keyTags);
        Cache::forget('user_role_2_' . $this->keyTags);
        Cache::forget('public_user_by_penggerak_' . $this->keyTags);

        if ($user->role_id == 2 && $user->status_aktif === 3) {
            $penggerakUsers = User::where('role_id', 3)->where('pj_pelaksana', $user->id)->get();
            foreach ($penggerakUsers as $penggerak) {
                $penggerak->status_aktif = 3;
                $penggerak->save();
            }
            $message .= " Semua pengguna dengan role Penggerak dari Penanggung Jawab '{$user->nama}' juga dinonaktifkan.";
        } elseif ($user->role_id == 2 && $user->status_aktif === 2) {
            $penggerakUsers = User::where('role_id', 3)->where('pj_pelaksana', $user->id)->get();
            foreach ($penggerakUsers as $penggerak) {
                $penggerak->status_aktif = 2;
                $penggerak->save();
            }
            $message .= " Semua pengguna dengan role Penggerak dari Penanggung Jawab '{$user->nama}' juga diaktifkan.";
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, $message), Response::HTTP_OK);
    }

    // Reset by admin
    public function resetPasswordPengguna($id)
    {
        if (!Gate::allows('reset password')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        // 1. Get user_id dari request
        $user = User::find($id);
        if (!$user) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Pengguna akun tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        // if ($user->status_aktif === 2) {
        //     return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Akun pengguna sedang bersatus aktif, Jika ingin mereset password silahkan nonaktifkan akun terlebih dahulu.'), Response::HTTP_FORBIDDEN);
        // }

        // 2. Pengecualian 'Super Admin'
        if ($user->id == 1 || $user->nama === 'Super Admin') {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Tidak diperbolehkan mereset password untuk akun Super Admin.'), Response::HTTP_FORBIDDEN);
        }

        // 3. Reset password
        $newPassword = RandomHelper::generatePasswordBasic();
        $user->password = Hash::make($newPassword);
        $user->save();

        Cache::forget('public_get_all_users_' . $this->keyTags);
        Cache::forget('user_role_1_' . $this->keyTags);
        Cache::forget('user_role_2_' . $this->keyTags);
        Cache::forget('public_user_by_penggerak_' . $this->keyTags);

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Berhasil melakukan reset password untuk pengguna '{$user->nama}'.",
        ], Response::HTTP_OK);
    }

    // Reset by user login
    public function updatePasswordPengguna(UpdateUserPasswordRequest $request)
    {
        if (!Gate::allows('update password')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $user = $this->loggedInUser;
        $data = $request->validated();
        if (isset($data['password'])) {
            $currentPassword = $request->input('current_password');
            if (!Hash::check($currentPassword, $user->password)) {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Kata sandi yang anda masukkan tidak valid.'), Response::HTTP_BAD_REQUEST);
            }

            $data['password'] = Hash::make($data['password']);
        }
        /** @var \App\Models\User $user **/
        $user->fill($data)->save();
        Cache::forget('public_get_all_users_' . $this->keyTags);
        Cache::forget('user_role_1_' . $this->keyTags);
        Cache::forget('user_role_2_' . $this->keyTags);
        Cache::forget('public_user_by_penggerak_' . $this->keyTags);
        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Berhasil memperbarui kata sandi anda.'), Response::HTTP_OK);
    }

    public function exportPengguna()
    {
        if (!Gate::allows('export pengguna')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $loggedInUser = $this->loggedInUser;

        $data_pengguna = User::all();
        if ($data_pengguna->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Tidak ada data pengguna yang tersedia untuk diekspor.'), Response::HTTP_NOT_FOUND);
        }

        try {
            return Excel::download(new PenggunaExport($loggedInUser), 'data-pengguna.xls');
        } catch (\Throwable $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Maaf sepertinya terjadi kesalahan.'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data pengguna berhasil di download.'), Response::HTTP_OK);
    }
}
