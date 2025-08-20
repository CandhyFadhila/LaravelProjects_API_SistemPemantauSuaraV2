<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KategoriSuara extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Get all of the suara_kpus for the KategoriSuara
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function suara_kpus(): HasMany
    {
        return $this->hasMany(SuaraKPU::class, 'kategori_suara_id', 'id');
    }
}
