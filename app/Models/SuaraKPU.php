<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SuaraKPU extends Model
{
    use HasFactory;

    protected $table = 'suara_kpus';
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'partai_id' => 'integer',
        'kelurahan_id' => 'integer',
        'tps' => 'integer',
        'jumlah_suara' => 'integer',
        'dpt_laki' => 'integer',
        'dpt_perempuan' => 'integer',
        'jumlah_dpt' => 'integer',
        'kategori_suara_id' => 'integer',
    ];

    /**
     * Get the partais that owns the SuaraKPU
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function partais(): BelongsTo
    {
        return $this->belongsTo(Partai::class, 'partai_id', 'id');
    }

    /**
     * Get the kelurahans that owns the SuaraKPU
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kelurahans(): BelongsTo
    {
        return $this->belongsTo(Kelurahan::class, 'kelurahan_id', 'id');
    }

    /**
     * Get the kategori_suaras that owns the SuaraKPU
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kategori_suaras(): BelongsTo
    {
        return $this->belongsTo(KategoriSuara::class, 'kategori_suara_id', 'id');
    }
}
