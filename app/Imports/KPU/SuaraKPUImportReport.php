<?php

namespace App\Imports\KPU;

class SuaraKPUImportReport
{
    /** @var array<string,array{name:string,sheet:string}> */
    public array $missingKecamatan = [];

    /** @var array<string,array{name:string,kecamatan:string,sheet:string}> */
    public array $missingKelurahan = [];

    public int $insertedRows = 0;
    public int $skippedRows  = 0;

    public function addMissingKecamatan(string $name, string $sheet): void
    {
        $key = mb_strtolower(trim($name)).'|'.mb_strtolower(trim($sheet));
        $this->missingKecamatan[$key] = ['name' => trim($name), 'sheet' => $sheet];
    }

    public function addMissingKelurahan(string $name, string $kecamatan, string $sheet): void
    {
        $key = mb_strtolower(trim($name)).'|'.mb_strtolower(trim($kecamatan)).'|'.mb_strtolower(trim($sheet));
        $this->missingKelurahan[$key] = [
            'name'      => trim($name),
            'kecamatan' => $kecamatan,
            'sheet'     => $sheet,
        ];
    }
}
