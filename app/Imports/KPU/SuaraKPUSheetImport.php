<?php

namespace App\Imports\KPU;

use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\Partai;
use App\Models\SuaraKPU;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;

class SuaraKPUSheetImport implements ToCollection, SkipsEmptyRows
{
    private int $tahun;
    private int $kategoriSuaraId;
    private string $sheetTitle;

    private int $partaiPas1Id;
    private int $partaiPas2Id;

    /** normalized_nama => id */
    private array $kecamatanMap;
    /** id => nama asli (untuk laporan) */
    private array $kecamatanNameById;

    /** (kecId|normalized_kel) => id  */
    private array $kelurahanMap;

    // kolom default
    private int $COL_KEC = 0, $COL_KEL = 1, $COL_TPS = 2, $COL_P1 = 3, $COL_P2 = 4;

    public function __construct(
        int $tahun,
        int $kategoriSuaraId,
        string $sheetTitle,
        private SuaraKPUImportReport $report
    ) {
        $this->tahun           = $tahun;
        $this->kategoriSuaraId = $kategoriSuaraId;
        $this->sheetTitle      = $sheetTitle;

        $this->partaiPas1Id = Partai::firstOrCreate(['nama' => 'PASLON 01'])->id;
        $this->partaiPas2Id = Partai::firstOrCreate(['nama' => 'PASLON 02'])->id;

        $this->kecamatanMap = [];
        $this->kecamatanNameById = [];
        foreach (Kecamatan::select('id', 'nama_kecamatan')->get() as $k) {
            $this->kecamatanNameById[$k->id] = $k->nama_kecamatan;
            foreach ($this->nameVariants($k->nama_kecamatan) as $key) {
                $this->kecamatanMap[$key] = $k->id;
            }
        }

        $this->kelurahanMap = [];
        foreach (Kelurahan::select('id', 'nama_kelurahan', 'kecamatan_id')->get() as $kel) {
            foreach ($this->nameVariants($kel->nama_kelurahan) as $key) {
                $this->kelurahanMap[$kel->kecamatan_id . '|' . $key] = $kel->id;
            }
        }
    }

    public function collection(Collection $rows): void
    {
        if ($rows->isEmpty()) {
            Log::warning("[SuaraKPU] Sheet '{$this->sheetTitle}' kosong.");
            return;
        }

        $headerRowIndex = $this->detectHeaderAndColumns($rows);

        // state fill-down
        $currentKecId = $this->lookupKecamatanId($this->sheetTitle);
        if (!$currentKecId) {
            // sheet title tidak cocok dengan DB → catat tapi tetap lanjut (siapa tahu ada kolom KECAMATAN valid di bawah)
            $this->report->addMissingKecamatan($this->sheetTitle, $this->sheetTitle);
        }
        $currentKel = null;

        $now   = now();
        $batch = [];
        $processed = 0;

        for ($i = $headerRowIndex + 1; $i < $rows->count(); $i++) {
            $row = $rows[$i];
            if ($row instanceof Collection) $row = $row->toArray();

            $kecName = $this->cell($row, $this->COL_KEC);
            $kelName = $this->cell($row, $this->COL_KEL);
            $tpsRaw  = $this->cell($row, $this->COL_TPS);
            $p1Raw   = $this->cell($row, $this->COL_P1);
            $p2Raw   = $this->cell($row, $this->COL_P2);

            // update kecamatan bila ada nilai baru
            if ($kecName !== null && $kecName !== '') {
                $found = $this->lookupKecamatanId((string)$kecName);
                if ($found) {
                    $currentKecId = $found;
                } else {
                    // tidak ditemukan → catat & JANGAN ganti currentKecId
                    $this->report->addMissingKecamatan((string)$kecName, $this->sheetTitle);
                }
            }
            // update kelurahan bila ada nilai baru
            if ($kelName !== null && $kelName !== '') {
                $currentKel = (string) $kelName;
            }

            $tps  = $this->toInt($tpsRaw);
            $pas1 = $this->toInt($p1Raw);
            $pas2 = $this->toInt($p2Raw);

            // baris kosong
            if (empty($tps) || ($pas1 === null && $pas2 === null)) {
                continue;
            }

            // kalau kecamatan masih tidak valid → skip & catat
            if (!$currentKecId) {
                $this->report->skippedRows++;
                // kalau ada nama kec yang terlihat di baris ini, sudah dicatat di atas; kalau tidak, catat generic
                $this->report->addMissingKecamatan($this->sheetTitle, $this->sheetTitle);
                continue;
            }
            // kelurahan kosong → skip & catat
            if (!$currentKel) {
                $this->report->skippedRows++;
                $this->report->addMissingKelurahan('(kosong)', $this->kecamatanNameById[$currentKecId] ?? 'UNKNOWN', $this->sheetTitle);
                continue;
            }

            $kelId = $this->lookupKelurahanId($currentKecId, $currentKel);
            if (!$kelId) {
                // tidak ketemu → catat & skip
                $this->report->skippedRows++;
                $this->report->addMissingKelurahan($currentKel, $this->kecamatanNameById[$currentKecId] ?? 'UNKNOWN', $this->sheetTitle);
                continue;
            }

            if ($pas1 !== null) {
                $batch[] = $this->rowPayload($this->partaiPas1Id, $kelId, $tps, $pas1, $now);
            }
            if ($pas2 !== null) {
                $batch[] = $this->rowPayload($this->partaiPas2Id, $kelId, $tps, $pas2, $now);
            }

            $processed++;

            if (\count($batch) >= 1000) {
                $this->report->insertedRows += \count($batch);
                $this->flush($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            $this->report->insertedRows += \count($batch);
            $this->flush($batch);
        }

        Log::info("[SuaraKPU] Sheet '{$this->sheetTitle}' processed={$processed} rows (max 2x per TPS).");
    }

    /** === helper deteksi header & kolom (sama seperti sebelumnya) === */
    private function detectHeaderAndColumns(Collection $rows): int
    {
        $limit = min(10, $rows->count());
        for ($i = 0; $i < $limit; $i++) {
            $row = $rows[$i];
            if ($row instanceof Collection) $row = $row->toArray();

            $norm = array_map(fn($v) => $this->normBase((string)($v ?? '')), $row);

            $idxTps = $this->firstIndex($norm, fn($s) => $s === 'tps');
            $idxP1  = $this->firstIndex($norm, fn($s) => str_contains($s, 'paslon') && str_contains($s, '01'));
            $idxP2  = $this->firstIndex($norm, fn($s) => str_contains($s, 'paslon') && str_contains($s, '02'));

            if ($idxTps !== null && ($idxP1 !== null || $idxP2 !== null)) {
                $this->COL_TPS = $idxTps;
                $this->COL_P1  = $idxP1 ?? ($idxTps + 1);
                $this->COL_P2  = $idxP2 ?? ($this->COL_P1 + 1);

                $idxKec = $this->firstIndex($norm, fn($s) => $s === 'kecamatan');
                $idxKel = $this->firstIndex($norm, fn($s) => $s === 'kelurahan');

                $this->COL_KEC = $idxKec ?? 0;
                $this->COL_KEL = $idxKel ?? 1;

                return $i;
            }
        }

        $this->COL_KEC = 0;
        $this->COL_KEL = 1;
        $this->COL_TPS = 2;
        $this->COL_P1  = 3;
        $this->COL_P2  = 4;
        return 0;
    }

    private function firstIndex(array $arr, callable $pred): ?int
    {
        foreach ($arr as $i => $v) if ($pred($v)) return $i;
        return null;
    }

    private function cell(array $row, int $index)
    {
        return array_key_exists($index, $row) ? $row[$index] : null;
    }

    /** lookup dengan 2 varian (spasi & tanpa spasi) */
    private function lookupKecamatanId(string $name): ?int
    {
        foreach ($this->nameVariants($name) as $key) {
            if (isset($this->kecamatanMap[$key])) return $this->kecamatanMap[$key];
        }
        return null;
    }

    private function lookupKelurahanId(int $kecId, string $name): ?int
    {
        foreach ($this->nameVariants($name) as $key) {
            $mapKey = $kecId . '|' . $key;
            if (isset($this->kelurahanMap[$mapKey])) return $this->kelurahanMap[$mapKey];
        }
        return null;
    }

    private function nameVariants(?string $s): array
    {
        $base = $this->normBase($s);
        $noSp = str_replace(' ', '', $base);
        return array_values(array_unique([$base, $noSp]));
    }

    private function normBase(?string $s): string
    {
        $s = trim((string) $s);
        $s = preg_replace('/\s+/u', ' ', $s);
        $s = Str::ascii($s);
        $s = mb_strtolower($s, 'UTF-8');
        $s = preg_replace('/[^a-z0-9 ]/u', '', $s);
        return $s;
    }

    private function rowPayload(int $partaiId, int $kelId, int $tps, int $jumlah, $now): array
    {
        return [
            'partai_id'         => $partaiId,
            'kelurahan_id'      => $kelId,
            'tahun'             => $this->tahun,
            'tps'               => $tps,
            'kategori_suara_id' => $this->kategoriSuaraId,
            'cakupan_wilayah'   => null,
            'alamat'            => null,
            'jumlah_suara'      => $jumlah,
            'dpt_laki'          => 0,
            'dpt_perempuan'     => 0,
            'jumlah_dpt'        => 0,
            'suara_caleg'       => 0,
            'suara_partai'      => 0,
            'created_at'        => $now,
            'updated_at'        => $now,
        ];
    }

    private function flush(array $batch): void
    {
        DB::transaction(function () use ($batch) {
            SuaraKPU::upsert(
                $batch,
                ['kelurahan_id', 'tps', 'partai_id', 'tahun', 'kategori_suara_id'],
                ['jumlah_suara', 'updated_at']
            );
        });
    }

    private function toInt($v): ?int
    {
        if ($v === null || $v === '') return null;
        if (is_numeric($v)) return (int) $v;
        $n = preg_replace('/\D+/', '', (string) $v);
        return $n === '' ? null : (int) $n;
    }
}
