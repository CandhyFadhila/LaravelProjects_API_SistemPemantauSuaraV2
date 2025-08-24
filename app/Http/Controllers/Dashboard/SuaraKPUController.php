<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\SuaraKPU;
use App\Models\Kelurahan;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Exports\KPU\SuaraKPUExport;
use App\Helpers\VersionedCacheHelper;
use App\Imports\KPU\SuaraKPUImport;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Http\Requests\KPU\ImportSuaraKPURequest;
use App\Http\Resources\public\WithoutDataResource;
use App\Imports\KPU\SuaraKPUImportReport;

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


            $file     = $request->validated();
            $uploaded = $file['kpu_file'];

            try {
                ini_set('max_execution_time', 500);

                $spreadsheet = IOFactory::load($uploaded->getRealPath());
                $sheetNames  = array_map(fn($s) => $s->getTitle(), $spreadsheet->getAllSheets());

                $tahun    = 2024;
                $kategori = 1;

                // shared report
                $report   = new SuaraKPUImportReport();
                $importer = new SuaraKPUImport($tahun, $kategori, $sheetNames, $report);

                Excel::import($importer, $uploaded);

                VersionedCacheHelper::bump('suara_kpu', 1);
            } catch (\Exception $e) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi kesalahan. ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
            }

            return response()->json([
                'status'  => Response::HTTP_OK,
                'message' => 'Data suara berhasil di import.',
                'data'    => [
                    'inserted_rows'      => $report->insertedRows,
                    'skipped_rows'       => $report->skippedRows,
                    'missing_kecamatan'  => array_values($report->missingKecamatan),
                    'missing_kelurahan'  => array_values($report->missingKelurahan),
                ],
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('| Suara KPU | - Error function importAktivitas: ' . $e->getMessage());
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
