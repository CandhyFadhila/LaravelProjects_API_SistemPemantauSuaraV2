<?php

namespace App\Http\Controllers\Dashboard;

use App\Exports\Saksi\AktivitasSaksiExport;
use App\Models\User;
use App\Models\Kelurahan;
use App\Helpers\DateHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Helpers\FileUploadHelper;
use App\Helpers\Filters\SaksiFilterHelper;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Helpers\VersionedCacheHelper;
use App\Http\Requests\Saksi\ImportAktivitasSaksiRequest;
use App\Http\Requests\Saksi\StoreAktivitasSaksiRequest;
use App\Http\Requests\Saksi\UpdateAktivitasSaksiRequest;
use App\Http\Resources\public\WithoutDataResource;
use App\Imports\Saksi\AktivitasSaksiImport;
use App\Models\AktivitasSaksi;
use App\Models\StatusAktivitasRw;
use Illuminate\Support\Arr;

class SaksiController extends Controller
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
        try {
            if (!Gate::allows('view aktivitasSaksi')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $limit = (int) $request->input('limit', 10);
            $page  = (int) $request->input('page', 1);
            $limit = $limit <= 0 ? 10 : $limit;
            $page  = $page <= 0 ? 1 : $page;
            $loggedInUser = $this->loggedInUser;

            $q = AktivitasSaksi::query()
                ->with([
                    'saksi_users.roles',
                    'kelurahans.provinsis',
                    'kelurahans.kabupaten_kotas',
                    'kelurahans.kecamatans',
                    'status',
                ])
                ->orderByDesc('created_at');

            // Filter by role
            if ($loggedInUser->role_id == 1) {
                // all
            } elseif ($loggedInUser->role_id == 2) {
                $q->where(function ($query) use ($loggedInUser) {
                    $query->whereHas('saksi_users', function ($subQuery) use ($loggedInUser) {
                        $subQuery->where('role_id', 4)->where('pj_pelaksana', $loggedInUser->id);
                    })->orWhere('saksi', $loggedInUser->id);
                });
            } elseif ($loggedInUser->role_id == 4) {
                $q->where('saksi', $loggedInUser->id);
            } else {
                return response()->json([
                    'status'  => Response::HTTP_FORBIDDEN,
                    'message' => 'Anda tidak memiliki hak akses untuk melihat aktivitas ini.'
                ], Response::HTTP_FORBIDDEN);
            }

            $filters = Arr::sortRecursive($request->except(['limit', 'page']));
            $q = SaksiFilterHelper::applyFiltersAktivitasSaksi($q, $filters);

            $routeKey = optional($request->route())->getName() ?? $request->path();
            $parts    = VersionedCacheHelper::standardPagedParts($routeKey, $loggedInUser->id, $loggedInUser->role_id, $filters, $page, $limit);

            $payload = VersionedCacheHelper::remember('saksi', $parts, function () use ($q, $limit, $page) {
                $p = $q->paginate($limit, ['*'], 'page', $page);
                return [
                    'items' => $p->items(),
                    'meta'  => [
                        'current_page' => $p->currentPage(),
                        'last_page'    => $p->lastPage(),
                        'per_page'     => $p->perPage(),
                        'total'        => $p->total(),
                    ],
                ];
            }, now()->addMinutes(30));

            $items = collect($payload['items']);
            $meta  = $payload['meta'];

            if ($items->isEmpty()) {
                return response()->json([
                    'status'     => Response::HTTP_NOT_FOUND,
                    'message'    => 'Data aktivitas saksi tidak ditemukan.',
                    'data'       => [],
                    'pagination' => [
                        'links' => [
                            'first' => null,
                            'last' => null,
                            'prev' => null,
                            'next' => null,
                        ],
                        'meta'  => $meta,
                    ],
                ], Response::HTTP_OK);
            }

            $formattedData = $items->map(function ($aktivitasSaksi) {
                $role = $aktivitasSaksi->saksi_users->roles->first();
                $saksi = $aktivitasSaksi->saksi_users;
                $kelurahanIds = $saksi ? $saksi->kelurahan_id ?? null : null;
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

                $pjPelaksana = $saksi && $saksi->pj_pelaksana ? User::find($saksi->pj_pelaksana) : null;
                $pjPelaksanaData = $pjPelaksana ? [
                    'id' => $pjPelaksana->id,
                    'nama' => $pjPelaksana->nama,
                    'username' => $pjPelaksana->username,
                    'email' => $pjPelaksana->email,
                    'no_kta' => $pjPelaksana->no_kta,
                    'alamat' => $pjPelaksana->alamat,
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
                    'id' => $aktivitasSaksi->id,
                    'saksi' => $aktivitasSaksi->saksi_users ? [
                        'id' => $aktivitasSaksi->saksi_users->id,
                        'nama' => $aktivitasSaksi->saksi_users->nama,
                        'username' => $aktivitasSaksi->saksi_users->username,
                        'email' => $aktivitasSaksi->saksi_users->email,
                        'no_kta' => $aktivitasSaksi->saksi_users->no_kta,
                        'alamat' => $aktivitasSaksi->saksi_users->alamat,
                        'nik_ktp' => $aktivitasSaksi->saksi_users->nik_ktp,
                        'foto_profil' =>  $aktivitasSaksi->saksi_users->foto_profil ? env('STORAGE_SERVER_DOMAIN') . $aktivitasSaksi->saksi_users->foto_profil : null,
                        'tgl_diangkat' => $aktivitasSaksi->saksi_users->tgl_diangkat,
                        'jenis_kelamin' => $aktivitasSaksi->saksi_users->jenis_kelamin,
                        'role' => $role ? [
                            'id' => $role->id,
                            'name' => $role->name,
                            'deskripsi' => $role->deskripsi,
                            'created_at' => $role->created_at,
                            'updated_at' => $role->updated_at,
                        ] : null,
                        'status_aktif' => $saksi->status_aktif,
                        'kelurahan' => $kelurahanData,
                        'rw_pelaksana' => $saksi->rw_pelaksana ?? null,
                        'pj_pelaksana' => $pjPelaksanaData,
                        'created_at' => $aktivitasSaksi->saksi_users->created_at,
                        'updated_at' => $aktivitasSaksi->saksi_users->updated_at
                    ] : null,
                    'deskripsi' => $aktivitasSaksi->deskripsi,
                    'tgl_mulai' => $aktivitasSaksi->tgl_mulai,
                    'tgl_selesai' => $aktivitasSaksi->tgl_selesai,
                    'tempat_aktivitas' => $aktivitasSaksi->tempat_aktivitas,
                    'foto_aktivitas' => $aktivitasSaksi->foto_aktivitas ? env('STORAGE_SERVER_DOMAIN') . $aktivitasSaksi->foto_aktivitas : null,
                    'rw' => $aktivitasSaksi->rw,
                    'kelurahan' => $aktivitasSaksi->kelurahans ? [
                        'id' => $aktivitasSaksi->kelurahans->id,
                        'nama_kelurahan' => $aktivitasSaksi->kelurahans->nama_kelurahan,
                        'kode_kelurahan' => $aktivitasSaksi->kelurahans->kode_kelurahan,
                        'max_rw' => $aktivitasSaksi->kelurahans->max_rw,
                        'kecamatan' => $aktivitasSaksi->kelurahans->kecamatans,
                        'kabupaten' => $aktivitasSaksi->kelurahans->kabupaten_kotas,
                        'provinsi' => $aktivitasSaksi->kelurahans->provinsis,
                        'created_at' => $aktivitasSaksi->kelurahans->created_at,
                        'updated_at' => $aktivitasSaksi->kelurahans->updated_at
                    ] : null,
                    'tps' => $aktivitasSaksi->tps,
                    'status_aktivitas' => $aktivitasSaksi->status ? [
                        'id' => $aktivitasSaksi->status->id,
                        'label' => $aktivitasSaksi->status->label,
                        'created_at' => $aktivitasSaksi->status->created_at,
                        'updated_at' => $aktivitasSaksi->status->updated_at
                    ] : null,
                    'status_aktivitas_rw' => $aktivitasSaksi->aktivitas_rws ? [
                        'id' => $aktivitasSaksi->aktivitas_rws->id,
                        'kelurahan' => $aktivitasSaksi->aktivitas_rws->kelurahans ? [
                            'id' => $aktivitasSaksi->aktivitas_rws->kelurahans->id,
                            'nama_kelurahan' => $aktivitasSaksi->aktivitas_rws->kelurahans->nama_kelurahan,
                            'kode_kelurahan' => $aktivitasSaksi->aktivitas_rws->kelurahans->kode_kelurahan,
                            'max_rw' => $aktivitasSaksi->aktivitas_rws->kelurahans->max_rw,
                            'kecamatan' => $aktivitasSaksi->aktivitas_rws->kelurahans->kecamatans,
                            'kabupaten' => $aktivitasSaksi->aktivitas_rws->kelurahans->kabupaten_kotas,
                            'provinsi' => $aktivitasSaksi->aktivitas_rws->kelurahans->provinsis,
                            'created_at' => $aktivitasSaksi->aktivitas_rws->kelurahans->created_at,
                            'updated_at' => $aktivitasSaksi->aktivitas_rws->kelurahans->updated_at
                        ] : null,
                        'rw' => $aktivitasSaksi->aktivitas_rws->rw,
                        'status_aktivitas' => $aktivitasSaksi->status ? [
                            'id' => $aktivitasSaksi->status->id,
                            'label' => $aktivitasSaksi->status->label,
                            'created_at' => $aktivitasSaksi->status->created_at,
                            'updated_at' => $aktivitasSaksi->status->updated_at
                        ] : null,
                    ] : null,
                    'created_at' => $aktivitasSaksi->created_at,
                    'updated_at' => $aktivitasSaksi->updated_at,
                ];
            });

            $paginationData = [
                'links' => [
                    'first' => $request->fullUrlWithQuery(['page' => 1, 'limit' => $meta['per_page']]),
                    'last'  => $request->fullUrlWithQuery(['page' => $meta['last_page'], 'limit' => $meta['per_page']]),
                    'prev'  => $meta['current_page'] > 1 ? $request->fullUrlWithQuery(['page' => $meta['current_page'] - 1, 'limit' => $meta['per_page']]) : null,
                    'next'  => $meta['current_page'] < $meta['last_page'] ? $request->fullUrlWithQuery(['page' => $meta['current_page'] + 1, 'limit' => $meta['per_page']]) : null,
                ],
                'meta' => $meta,
            ];

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'Data aktivitas saksi berhasil ditampilkan.',
                'data' => $formattedData,
                'pagination' => $paginationData
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::channel('aktivitas_saksi')->error('| Index | - Error function index : ' . $e->getMessage() . ' - Line : ' . $e->getLine());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada sistem, silahkan coba lagi nanti atau hubungi admin.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(StoreAktivitasSaksiRequest $request)
    {
        try {
            if (!Gate::allows('create aktivitasSaksi')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $validatedData = $request->validated();

            // Handle file upload untuk foto aktivitas
            $fotoAktivitasPath = null;
            if ($request->hasFile('foto_aktivitas')) {
                $fotoAktivitasPath = FileUploadHelper::storePhoto($request->file('foto_aktivitas'), 'aktivitas');
            }

            $kelurahan = Kelurahan::where('kode_kelurahan', $validatedData['kelurahan_id'])->first();
            if (!$kelurahan) {
                return response()->json([
                    'status' => Response::HTTP_BAD_REQUEST,
                    'message' => "Kelurahan dengan kode '{$validatedData['kelurahan_id']}' tidak ditemukan."
                ], Response::HTTP_BAD_REQUEST);
            }

            $aktivitasSaksi = AktivitasSaksi::create([
                'saksi' => $validatedData['saksi_id'],
                'status_aktivitas' => $validatedData['status_aktivitas'],
                'deskripsi' => $validatedData['deskripsi'] ?? null,
                'tgl_mulai' => $validatedData['tgl_mulai'],
                'tgl_selesai' => $validatedData['tgl_selesai'],
                'tempat_aktivitas' => $validatedData['tempat_aktivitas'],
                'foto_aktivitas' => $fotoAktivitasPath,
                'rw' => $validatedData['rw'],
                'tps' => $validatedData['tps'],
                'kelurahan' => $kelurahan->id,
            ]);

            // 1. cek kelurahan dan rw ada tidak
            $statusAktivitasRw = StatusAktivitasRw::where('kelurahan_id', $kelurahan->id)
                // ->where('status_aktivitas', $validatedData['status_aktivitas'])
                ->where('rw', $validatedData['rw'])
                ->first();
            if ($statusAktivitasRw) {
                // Jika ada, update status_aktivitas
                $statusAktivitasRw->update([
                    'status_aktivitas' => $validatedData['status_aktivitas']
                ]);
            } else {
                // Jika belum ada, buat baru di status_aktivitas_rws
                $statusAktivitasRw = StatusAktivitasRw::create([
                    'kelurahan_id' => $kelurahan->id,
                    'rw' => $validatedData['rw'],
                    'status_aktivitas' => $validatedData['status_aktivitas']
                ]);
            }

            // 2. abistu update status_aktivitas_rw ids
            $aktivitasSaksi->update([
                'status_aktivitas_rw' => $statusAktivitasRw->id
            ]);

            $tanggal_aktivitas = DateHelper::convertToDMY($aktivitasSaksi->tgl_mulai);
            return response()->json([
                'status' => Response::HTTP_CREATED,
                'message' => "Aktivitas saksi pada RW {$aktivitasSaksi->rw} Kelurahan '{$aktivitasSaksi->kelurahans->nama_kelurahan}' tanggal {$tanggal_aktivitas} berhasil ditambahkan.",
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            Log::channel('aktivitas_saksi')->error('| Store | - Error function store : ' . $e->getMessage() . ' - Line : ' . $e->getLine());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada sistem, silahkan coba lagi nanti atau hubungi admin.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($id)
    {
        try {
            if (!Gate::allows('view aktivitasSaksi')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $aktivitasSaksi = AktivitasSaksi::find($id);
            if (!$aktivitasSaksi) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Aktivitas saksi tidak ditemukan.',
                ], Response::HTTP_NOT_FOUND);
            }

            $role = $aktivitasSaksi->saksi_users->roles->first();
            $saksi = $aktivitasSaksi->saksi_users;
            $kelurahanIds = $saksi ? $saksi->kelurahan_id ?? null : null;
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

            $pjPelaksana = $saksi && $saksi->pj_pelaksana ? User::find($saksi->pj_pelaksana) : null;
            $pjPelaksanaData = $pjPelaksana ? [
                'id' => $pjPelaksana->id,
                'nama' => $pjPelaksana->nama,
                'username' => $pjPelaksana->username,
                'email' => $pjPelaksana->email,
                'no_kta' => $pjPelaksana->no_kta,
                'alamat' => $pjPelaksana->alamat,
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
                'id' => $aktivitasSaksi->id,
                'deskripsi' => $aktivitasSaksi->deskripsi,
                'tgl_mulai' => $aktivitasSaksi->tgl_mulai,
                'tgl_selesai' => $aktivitasSaksi->tgl_selesai,
                'tempat_aktivitas' => $aktivitasSaksi->tempat_aktivitas,
                'foto_aktivitas' => $aktivitasSaksi->foto_aktivitas ? env('STORAGE_SERVER_DOMAIN') . $aktivitasSaksi->foto_aktivitas : null,
                'saksi' => $aktivitasSaksi->saksi_users ? [
                    'id' => $aktivitasSaksi->saksi_users->id,
                    'nama' => $aktivitasSaksi->saksi_users->nama,
                    'username' => $aktivitasSaksi->saksi_users->username,
                    'email' => $aktivitasSaksi->saksi_users->email,
                    'no_kta' => $aktivitasSaksi->saksi_users->no_kta,
                    'alamat' => $aktivitasSaksi->saksi_users->alamat,
                    'nik_ktp' => $aktivitasSaksi->saksi_users->nik_ktp,
                    'foto_profil' =>  $aktivitasSaksi->saksi_users->foto_profil ? env('STORAGE_SERVER_DOMAIN') . $aktivitasSaksi->saksi_users->foto_profil : null,
                    'tgl_diangkat' => $aktivitasSaksi->saksi_users->tgl_diangkat,
                    'jenis_kelamin' => $aktivitasSaksi->saksi_users->jenis_kelamin,
                    'role' => $role ? [
                        'id' => $role->id,
                        'name' => $role->name,
                        'deskripsi' => $role->deskripsi,
                        'created_at' => $role->created_at,
                        'updated_at' => $role->updated_at,
                    ] : null,
                    'status_aktif' => $saksi->status_aktif,
                    'kelurahan' => $kelurahanData,
                    'rw_pelaksana' => $saksi->rw_pelaksana ?? null,
                    'pj_pelaksana' => $pjPelaksanaData,
                    'created_at' => $aktivitasSaksi->saksi_users->created_at,
                    'updated_at' => $aktivitasSaksi->saksi_users->updated_at
                ] : null,
                'status_aktivitas' => $aktivitasSaksi->status,
                'rw' => $aktivitasSaksi->rw,
                'kelurahan' => $aktivitasSaksi->kelurahans ? [
                    'id' => $aktivitasSaksi->kelurahans->id,
                    'nama_kelurahan' => $aktivitasSaksi->kelurahans->nama_kelurahan,
                    'kode_kelurahan' => $aktivitasSaksi->kelurahans->kode_kelurahan,
                    'max_rw' => $aktivitasSaksi->kelurahans->max_rw,
                    'provinsi_id' => $aktivitasSaksi->kelurahans->provinsis,
                    'kabupaten_id' => $aktivitasSaksi->kelurahans->kabupaten_kotas,
                    'kecamatan_id' => $aktivitasSaksi->kelurahans->kecamatans,
                    'created_at' => $aktivitasSaksi->kelurahans->created_at,
                    'updated_at' => $aktivitasSaksi->kelurahans->updated_at
                ] : null,
                'tps' => $aktivitasSaksi->tps,
                'status_aktivitas_rw' => $aktivitasSaksi->aktivitas_rws ? [
                    'id' => $aktivitasSaksi->aktivitas_rws->id,
                    'kelurahan' => $aktivitasSaksi->aktivitas_rws->kelurahans ? [
                        'id' => $aktivitasSaksi->aktivitas_rws->kelurahans->id,
                        'nama_kelurahan' => $aktivitasSaksi->aktivitas_rws->kelurahans->nama_kelurahan,
                        'kode_kelurahan' => $aktivitasSaksi->aktivitas_rws->kelurahans->kode_kelurahan,
                        'max_rw' => $aktivitasSaksi->aktivitas_rws->kelurahans->max_rw,
                        'kecamatan' => $aktivitasSaksi->aktivitas_rws->kelurahans->kecamatans,
                        'kabupaten' => $aktivitasSaksi->aktivitas_rws->kelurahans->kabupaten_kotas,
                        'provinsi' => $aktivitasSaksi->aktivitas_rws->kelurahans->provinsis,
                        'created_at' => $aktivitasSaksi->aktivitas_rws->kelurahans->created_at,
                        'updated_at' => $aktivitasSaksi->aktivitas_rws->kelurahans->updated_at
                    ] : null,
                    'rw' => $aktivitasSaksi->aktivitas_rws->rw,
                    'status_aktivitas' => $aktivitasSaksi->status ? [
                        'id' => $aktivitasSaksi->status->id,
                        'label' => $aktivitasSaksi->status->label,
                        'created_at' => $aktivitasSaksi->status->created_at,
                        'updated_at' => $aktivitasSaksi->status->updated_at
                    ] : null,
                ] : null,
                'created_at' => $aktivitasSaksi->created_at,
                'updated_at' => $aktivitasSaksi->updated_at,
            ];
            $tanggal_aktivitas = DateHelper::convertToDMY($aktivitasSaksi->tgl_mulai);
            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => "Aktivitas saksi pada RW {$aktivitasSaksi->rw} Kelurahan '{$aktivitasSaksi->kelurahans->nama_kelurahan}' tanggal '{$tanggal_aktivitas}' berhasil ditampilkan.",
                'data' => $formattedData,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::channel('aktivitas_saksi')->error('| Show | - Error function show : ' . $e->getMessage() . ' - Line : ' . $e->getLine());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada sistem, silahkan coba lagi nanti atau hubungi admin.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(UpdateAktivitasSaksiRequest $request, $id)
    {
        try {
            if (!Gate::allows('edit aktivitasSaksi')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $aktivitasSaksi = AktivitasSaksi::find($id);
            if (!$aktivitasSaksi) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Aktivitas saksi tidak ditemukan.',
                ], Response::HTTP_NOT_FOUND);
            }

            $validatedData = $request->validated();

            // Update data aktivitas
            $aktivitasSaksi->deskripsi = $validatedData['deskripsi'] ?? $aktivitasSaksi->deskripsi;
            $aktivitasSaksi->tgl_mulai = $validatedData['tgl_mulai'] ?? $aktivitasSaksi->tgl_mulai;
            $aktivitasSaksi->tgl_selesai = $validatedData['tgl_selesai'] ?? $aktivitasSaksi->tgl_selesai;
            $aktivitasSaksi->tempat_aktivitas = $validatedData['tempat_aktivitas'] ?? $aktivitasSaksi->tempat_aktivitas;
            $aktivitasSaksi->tps = $validatedData['tps'] ?? $aktivitasSaksi->tps;

            // Jika ada file foto aktivitas baru, simpan dan hapus yang lama
            if ($request->hasFile('foto_aktivitas')) {
                if ($aktivitasSaksi->foto_aktivitas) {
                    FileUploadHelper::deletePhoto($aktivitasSaksi->foto_aktivitas);
                }
                $aktivitasSaksi->foto_aktivitas = FileUploadHelper::storePhoto($request->file('foto_aktivitas'), 'aktivitas');
            } else if (is_string($request->input('foto_aktivitas'))) {
                unset($validatedData['foto_aktivitas']);
            }

            $aktivitasSaksi->save();

            $tanggal_aktivitas = DateHelper::convertToDMY($aktivitasSaksi->tgl_mulai);
            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => "Aktivitas saksi pada RW {$aktivitasSaksi->rw} Kelurahan '{$aktivitasSaksi->kelurahans->nama_kelurahan}' tanggal {$tanggal_aktivitas} berhasil diperbarui."
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::channel('aktivitas_saksi')->error('| Update | - Error function update : ' . $e->getMessage() . ' - Line : ' . $e->getLine());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada sistem, silahkan coba lagi nanti atau hubungi admin.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy($id)
    {
        try {
            if (!Gate::allows('delete aktivitasSaksi')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $aktivitasSaksi = AktivitasSaksi::find($id);
            if (!$aktivitasSaksi) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Aktivitas saksi tidak ditemukan.',
                ], Response::HTTP_NOT_FOUND);
            }

            $aktivitasSaksi->delete();
            $tanggal_aktivitas = DateHelper::convertToDMY($aktivitasSaksi->tgl_mulai);
            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => "Aktivitas saksi pada RW {$aktivitasSaksi->rw} Kelurahan '{$aktivitasSaksi->kelurahans->nama_kelurahan}' tanggal '{$tanggal_aktivitas}' berhasil dihapus.",
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::channel('aktivitas_saksi')->error('| Destroy | - Error function destroy : ' . $e->getMessage() . ' - Line : ' . $e->getLine());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada sistem, silahkan coba lagi nanti atau hubungi admin.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function exportAktivitasSaksi()
    {
        try {
            if (!Gate::allows('export aktivitasSaksi')) {
                return response()->json(new WithoutDataResource(
                    Response::HTTP_FORBIDDEN,
                    'Anda tidak memiliki hak akses untuk melakukan proses ini.'
                ), Response::HTTP_FORBIDDEN);
            }

            $loggedInUser = auth()->user();

            $data_aktivitas = AktivitasSaksi::all();
            if ($data_aktivitas->isEmpty()) {
                return response()->json(new WithoutDataResource(
                    Response::HTTP_NOT_FOUND,
                    'Tidak ada data aktivitas yang tersedia untuk diekspor.'
                ), Response::HTTP_NOT_FOUND);
            }

            try {
                return Excel::download(new AktivitasSaksiExport($loggedInUser), 'data-aktivitas-saksi.xls');
            } catch (\Throwable $e) {
                return response()->json(new WithoutDataResource(
                    Response::HTTP_INTERNAL_SERVER_ERROR,
                    'Maaf sepertinya terjadi kesalahan.'
                ), Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return response()->json(new WithoutDataResource(
                Response::HTTP_OK,
                'Data pengguna berhasil di download.'
            ), Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::channel('aktivitas_saksi')->error('| Export | - Error function export : ' . $e->getMessage() . ' - Line : ' . $e->getLine());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada sistem, silahkan coba lagi nanti atau hubungi admin.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function importAktivitasSaksi(ImportAktivitasSaksiRequest $request)
    {
        try {
            if (!Gate::allows('import aktivitasSaksi')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $file = $request->validated();

            try {
                Excel::import(new AktivitasSaksiImport, $file['aktivitas_saksi_file']);
                VersionedCacheHelper::bump(AktivitasSaksi::CACHE_NAMESPACE, 1);
            } catch (\Exception $e) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi kesalahan.' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
            }

            return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data aktivitas saksi berhasil di import kedalam database.'), Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::channel('aktivitas_saksi')->error('| Import | - Error function import : ' . $e->getMessage() . ' - Line : ' . $e->getLine());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada sistem, silahkan coba lagi nanti atau hubungi admin.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
