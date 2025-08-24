<?php

namespace App\Imports\KPU;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SuaraKPUImport implements WithMultipleSheets
{
    public function __construct(
        private int $tahun,
        private int $kategoriSuaraId,
        private array $sheetNames,
        public SuaraKPUImportReport $report
    ) {}

    public function sheets(): array
    {
        $map = [];
        foreach ($this->sheetNames as $name) {
            $map[$name] = new SuaraKPUSheetImport(
                tahun: $this->tahun,
                kategoriSuaraId: $this->kategoriSuaraId,
                sheetTitle: $name,
                report: $this->report
            );
        }
        return $map;
    }
}
