<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Provinsi extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $casts = [
        'id' => 'integer',
    ];

    /**
     * Get all of the kabupaten_kotas for the Provinsi
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function kabupaten_kotas(): HasMany
    {
        return $this->hasMany(Kabupaten::class, 'provinsi_id', 'id');
    }

    /**
     * Get all of the kecamatans for the Provinsi
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function kecamatans(): HasMany
    {
        return $this->hasMany(Kecamatan::class, 'provinsi_id', 'id');
    }

    /**
     * Get all of the kelurahans for the Provinsi
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function kelurahans(): HasMany
    {
        return $this->hasMany(Kelurahan::class, 'provinsi_id', 'id');
    }
}
