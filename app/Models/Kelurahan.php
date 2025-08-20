<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kelurahan extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $casts = [
        'id' => 'integer',
        'max_rw' => 'integer',
        'provinsi_id' => 'integer',
        'kabupaten_id' => 'integer',
        'kecamatan_id' => 'integer',
    ];

    /**
     * Get the provinsis that owns the Kelurahan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function provinsis(): BelongsTo
    {
        return $this->belongsTo(Provinsi::class, 'provinsi_id', 'id');
    }

    /**
     * Get the kabupaten_kotas that owns the Kelurahan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kabupaten_kotas(): BelongsTo
    {
        return $this->belongsTo(Kabupaten::class, 'kabupaten_id', 'id');
    }

    /**
     * Get the kecamatans that owns the Kelurahan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kecamatans(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class, 'kecamatan_id', 'id');
    }

    /**
     * Get all of the aktivitas_pelaksanas for the Kelurahan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function aktivitas_pelaksanas(): HasMany
    {
        return $this->hasMany(AktivitasPelaksana::class, 'kelurahan', 'id');
    }

    /**
     * Get all of the suara_kpus for the Kelurahan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function suara_kpus(): HasMany
    {
        return $this->hasMany(SuaraKPU::class, 'kelurahan_id', 'id');
    }

    /**
     * Get all of the potensi_tps for the Kelurahan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function potensi_tps(): HasMany
    {
        return $this->hasMany(UpcomingTps::class, 'kelurahan_id', 'id');
    }

    /**
     * Get the status_aktivitas_rw associated with the Kelurahan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function status_aktivitas_rw(): HasOne
    {
        return $this->hasOne(StatusAktivitasRw::class, 'kelurahan_id', 'id');
    }
}
