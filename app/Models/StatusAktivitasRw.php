<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StatusAktivitasRw extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'kelurahan_id' => 'integer',
        'rw' => 'integer',
        'status_aktivitas' => 'integer',
    ];

    /**
     * Get the kelurahans that owns the StatusAktivitasRw
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kelurahans(): BelongsTo
    {
        return $this->belongsTo(Kelurahan::class, 'kelurahan_id', 'id');
    }

    /**
     * Get the status_aktivitas that owns the StatusAktivitasRw
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function aktivitas_status(): BelongsTo
    {
        return $this->belongsTo(StatusAktivitas::class, 'status_aktivitas', 'id');
    }

    /**
     * Get the aktivitas_rws associated with the StatusAktivitasRw
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function aktivitas_rws(): HasOne
    {
        return $this->hasOne(AktivitasPelaksana::class, 'status_aktivitas_rw', 'id');
    }
}
