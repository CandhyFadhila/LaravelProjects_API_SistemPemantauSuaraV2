<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Partai extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
    ];

    /**
     * Get all of the suara_kpus for the Partai
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function suara_kpus(): HasMany
    {
        return $this->hasMany(SuaraKPU::class, 'partai_id', 'id');
    }
}
