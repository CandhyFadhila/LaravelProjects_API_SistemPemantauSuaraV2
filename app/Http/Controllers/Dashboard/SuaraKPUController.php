<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\SuaraKPU;
use App\Models\Kelurahan;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Exports\KPU\SuaraKPUExport;
use App\Imports\KPU\SuaraKPUImport;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\KPU\ImportSuaraKPURequest;
use App\Http\Resources\public\WithoutDataResource;

class SuaraKPUController extends Controller
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

    public function exportKPU(Request $request)
    {
        try {
            if (!Gate::allows('export aktivitas')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $data_suara_kpu = SuaraKPU::all();
            if ($data_suara_kpu->isEmpty()) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Tidak ada data suara yang tersedia untuk diekspor.'), Response::HTTP_NOT_FOUND);
            }

            try {
                return Excel::download(new SuaraKPUExport($request->all()), 'data-suaraKPU.xls');
            } catch (\Throwable $e) {
                return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Maaf sepertinya terjadi kesalahan.'), Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data suara berhasil di download.'), Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Suara KPU | - Error function exportKPU: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function importKPU(ImportSuaraKPURequest $request)
    {
        try {
            if (!Gate::allows('import suaraKPU')) {
                return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
            }

            $loggedInUser = $this->loggedInUser;

            $file = $request->validated();

            if ($loggedInUser->role_id == 1) {
                $kelurahan = Kelurahan::all();
            }
            if ($loggedInUser->kelurahan_id && !empty($loggedInUser->kelurahan_id)) {
                $kelurahan = Kelurahan::whereIn('id', $loggedInUser->kelurahan_id)->get();
            }

            try {
                ini_set('max_execution_time', 500);
                Excel::import(new SuaraKPUImport, $file['kpu_file']);

                foreach ($kelurahan as $kel) {
                    Cache::forget('public_suara_kpu_' . $this->keyTags . '_' . $kel->kode_kelurahan);
                }
            } catch (\Exception $e) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi kesalahan.' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
            }

            return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data suara berhasil di import kedalam database.'), Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Suara KPU | - Error function importAktivitas: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
