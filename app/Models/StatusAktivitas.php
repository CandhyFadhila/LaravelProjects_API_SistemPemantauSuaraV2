<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StatusAktivitas extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Get all of the aktivitas_pelaksanas for the StatusAktivitas
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function aktivitas_pelaksanas(): HasMany
    {
        return $this->hasMany(AktivitasPelaksana::class, 'status_aktivitas', 'id');
    }

    /**
     * Get all of the status_aktivitas_rw for the StatusAktivitas
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function status_aktivitas_rw(): HasMany
    {
        return $this->hasMany(StatusAktivitasRw::class, 'status_aktivitas', 'id');
    }
}
