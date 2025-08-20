<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UpcomingTps extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'kelurahan_id' => 'integer',
        'tahun' => 'integer',
        'jumlah_tps' => 'integer',
    ];

    /**
     * Get the kelurahans that owns the UpcomingTps
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kelurahans(): BelongsTo
    {
        return $this->belongsTo(Kelurahan::class, 'kelurahan_id', 'id');
    }
}
