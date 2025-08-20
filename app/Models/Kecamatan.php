<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kecamatan extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $casts = [
        'id' => 'integer',
        'kabupaten_id' => 'integer',
        'provinsi_id' => 'integer',
    ];

    /**
     * Get the kabupaten_kotas that owns the Kecamatan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kabupaten_kotas(): BelongsTo
    {
        return $this->belongsTo(Kabupaten::class, 'kabupaten_id', 'id');
    }

    /**
     * Get the provinsis that owns the Kecamatan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function provinsis(): BelongsTo
    {
        return $this->belongsTo(Provinsi::class, 'provinsi_id', 'id');
    }

    /**
     * Get all of the kelurahans for the Kecamatan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function kelurahans(): HasMany
    {
        return $this->hasMany(Kelurahan::class, 'kecamatan_id', 'id');
    }
}
