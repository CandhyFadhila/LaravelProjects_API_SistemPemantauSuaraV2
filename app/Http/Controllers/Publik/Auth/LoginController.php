<?php

namespace App\Http\Controllers\Publik\Auth;

use App\Models\User;
use App\Models\Kelurahan;
use Illuminate\Support\Str;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cookie;
use App\Http\Requests\Login\LoginRequest;
use App\Http\Resources\public\WithoutDataResource;

class LoginController extends Controller
{
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        $user = User::where('username', $credentials['username'])->first();
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            Log::error("Login failed for username: {$credentials['username']} - Invalid credentials.");
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Username atau password Anda tidak valid, silakan periksa kembali dan lakukan login ulang.'), Response::HTTP_BAD_REQUEST);
        }

        // Cek status_aktif
        if (in_array($user->status_aktif, [1, 3])) {
            auth()->logout();
            $inactiveSince = $user->updated_at ?? $user->created_at;
            Log::info("| Auth | - Login failed for user ID: {$user->id} - Account inactive since {$inactiveSince}.");
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, "Kami mendeteksi bahwa akun anda belum/tidak aktif sejak {$inactiveSince}."), Response::HTTP_FORBIDDEN);
        }

        Log::info("Login successful for user ID: {$user->id}, Name: {$user->nama}.");

        $isAdmin = $user->hasRole(['Super Admin', 'Penanggung Jawab']) || in_array($user->role_id, [1, 2]);

        // Generate token
        $token = $user->createToken('create_token_' . Str::uuid())->plainTextToken;

        $role = $user->roles->first();

        $kelurahanIds = $user->kelurahan_id ?? null;
        $rwPelaksana = $user->rw_pelaksana ?? null;

        // Jika ada kelurahan_id, ambil detail kelurahan dari database
        $kelurahanDetails = null;
        if (!empty($kelurahanIds)) {
            $kelurahanDetails = Kelurahan::whereIn('id', $kelurahanIds)->get()->map(function ($kelurahan) {
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

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Login berhasil! Selamat datang '{$user->nama}'.",
            'data' => [
                'user' => [
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
                    'is_admin' => $isAdmin,
                    'kelurahan' => $kelurahanDetails,
                    'rw_pelaksana' => $rwPelaksana,
                    'pj_pelaksana' => $pjPelaksanaData,
                    'status_aktif' => $user->status_users ? [
                        'id' => $user->status_users->id,
                        'label' => $user->status_users->label,
                        'created_at' => $user->status_users->created_at,
                        'updated_at' => $user->status_users->updated_at
                    ] : null,
                    'permission' => $role ? $role->permissions->pluck('id') : [],
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ],
                'token' => $token,
            ]
        ], Response::HTTP_OK);
    }

    public function getInfoUserLogin()
    {
        $user = auth()->user();
        $role = $user->roles->first();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Berhasil menampilkan detail akun '{$user->nama}'.",
            'data' => [
                'user' => [
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
                    'status_aktif' => $user->status_aktif,
                    'permission' => $role ? $role->permissions->pluck('id') : [],
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ],
            ]
        ], Response::HTTP_OK);
    }

    public function logout()
    {
        $user = auth()->user();

        if (method_exists($user->currentAccessToken(), 'delete')) {
            $user->currentAccessToken()->delete();
        }

        auth()->guard('web')->logout();
        $deleteCookie = Cookie::forget('authToken');

        Log::info("Logout successful for user ID: {$user->id}, Name: {$user->nama}.");

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Anda berhasil melakukan logout.'), Response::HTTP_OK)->withCookie($deleteCookie);
    }
}
