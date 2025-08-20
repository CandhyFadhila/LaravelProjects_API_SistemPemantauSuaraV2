<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\UpcomingTps;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\KPU\StoreUpcomingTPSRequest;
use App\Http\Resources\public\WithoutDataResource;
use App\Http\Requests\KPU\UpdateUpcomingTPSRequest;

class UpcomingTPSController extends Controller
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

    public function store(StoreUpcomingTPSRequest $request)
    {
        try {
            if (!Gate::allows('create upcomingTPS')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $validatedData = $request->validated();

            $upcomingTPS = UpcomingTps::create([
                'kelurahan_id' => $validatedData['kelurahan_id'],
                'tahun' => $validatedData['tahun'],
                'jumlah_tps' => $validatedData['jumlah_tps'],
            ]);

            Cache::forget('public_get_all_data_upcoming_tps_' . $this->keyTags);

            return response()->json([
                'status' => Response::HTTP_CREATED,
                'message' => "Data TPS mendatang untuk kelurahan '{$upcomingTPS->kelurahans->nama_kelurahan}' di tahun '{$upcomingTPS->tahun}' berhasil ditambahkan.",
                'data' => $upcomingTPS
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            Log::error('| UpcomingTPS | - Error function create: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($id)
    {
        try {
            if (!Gate::allows('view upcomingTPS')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melihat data ini.'), Response::HTTP_FORBIDDEN);
            }

            $upcomingTPS = UpcomingTPS::find($id);
            if (!$upcomingTPS) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Data TPS tidak ditemukan.',
                ], Response::HTTP_NOT_FOUND);
            }

            $formattedData =  [
                'id' => $upcomingTPS->id,
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
                'updated_at' => $upcomingTPS->updated_at,
            ];

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => "Data TPS mendatang untuk kelurahan '{$upcomingTPS->kelurahans->nama_kelurahan}' di tahun '{$upcomingTPS->tahun}' berhasil ditampilkan.",
                'data' => $formattedData
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| UpcomingTPS | - Error function show: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(UpdateUpcomingTPSRequest $request, $id)
    {
        try {
            if (!Gate::allows('edit upcomingTPS')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk memperbarui data ini.'), Response::HTTP_FORBIDDEN);
            }

            $validatedData = $request->validated();

            $upcomingTPS = UpcomingTPS::find($id);
            if (!$upcomingTPS) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Data TPS tidak ditemukan.',
                ], Response::HTTP_NOT_FOUND);
            }

            // Perbarui data
            $upcomingTPS->kelurahan_id = $validatedData['kelurahan_id'] ?? $upcomingTPS->kelurahan_id;
            $upcomingTPS->tahun = $validatedData['tahun'] ?? $upcomingTPS->tahun;
            $upcomingTPS->jumlah_tps = $validatedData['jumlah_tps'] ?? $upcomingTPS->jumlah_tps;
            $upcomingTPS->save();

            Cache::forget('public_get_all_data_upcoming_tps_' . $this->keyTags);

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => "Data TPS mendatang untuk kelurahan '{$upcomingTPS->kelurahans->nama_kelurahan}' di tahun '{$upcomingTPS->tahun}' berhasil diperbarui.",
                'data' => $upcomingTPS
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| UpcomingTPS | - Error function update: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy($id)
    {
        try {
            if (!Gate::allows('delete upcomingTPS')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk menghapus data ini.'), Response::HTTP_FORBIDDEN);
            }

            $upcomingTPS = UpcomingTPS::find($id);
            if (!$upcomingTPS) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Data TPS tidak ditemukan.',
                ], Response::HTTP_NOT_FOUND);
            }

            $upcomingTPS->delete();

            Cache::forget('public_get_all_data_upcoming_tps_' . $this->keyTags);

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => "Data TPS mendatang untuk kelurahan '{$upcomingTPS->kelurahans->nama_kelurahan}' di tahun '{$upcomingTPS->tahun}' berhasil dihapus.",
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| UpcomingTPS | - Error function destroy: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
