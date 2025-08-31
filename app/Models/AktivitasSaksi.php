<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AktivitasSaksi extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $casts = [
        'id' => 'integer',
        'saksi' => 'integer',
        'status_aktivitas' => 'integer',
        'status_aktivitas_rw' => 'integer',
        'kelurahan' => 'integer',
        'rw' => 'integer',
        'tps' => 'integer',
    ];

    /**
     * Get the saksi_users that owns the AktivitasPelaksana
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function saksi_users(): BelongsTo
    {
        return $this->belongsTo(User::class, 'saksi', 'id');
    }

    /**
     * Get the status_aktivitas that owns the AktivitasPelaksana
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(StatusAktivitas::class, 'status_aktivitas', 'id');
    }

    /**
     * Get the kelurahans that owns the AktivitasPelaksana
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kelurahans(): BelongsTo
    {
        return $this->belongsTo(Kelurahan::class, 'kelurahan', 'id');
    }

    /**
     * Get the aktivitas_rws that owns the AktivitasPelaksana
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function aktivitas_rws(): BelongsTo
    {
        return $this->belongsTo(StatusAktivitasRw::class, 'status_aktivitas_rw', 'id');
    }
}
