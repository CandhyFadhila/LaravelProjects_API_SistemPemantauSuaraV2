<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kabupaten extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $casts = [
        'id' => 'integer',
        'provinsi_id' => 'integer',
    ];

    /**
     * Get the provinsis that owns the Kabupaten
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function provinsis(): BelongsTo
    {
        return $this->belongsTo(Provinsi::class, 'provinsi_id', 'id');
    }

    /**
     * Get all of the kecamatans for the Kabupaten
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function kecamatans(): HasMany
    {
        return $this->hasMany(Kecamatan::class, 'kabupaten_id', 'id');
    }

    /**
     * Get all of the kelurahans for the Kabupaten
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function kelurahans(): HasMany
    {
        return $this->hasMany(Kelurahan::class, 'kabupaten_id', 'id');
    }
}
