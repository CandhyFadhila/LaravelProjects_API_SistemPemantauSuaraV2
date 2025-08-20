<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $guarded = ['id'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'integer',
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'role_id' => 'integer',
        'status_aktif' => 'integer',
        'pj_pelaksana' => 'integer',
        'kelurahan_id' => 'array',
        'rw_pelaksana' => 'array'
    ];

    /**
     * Get all of the aktivitas_users for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function aktivitas_users(): HasMany
    {
        return $this->hasMany(AktivitasPelaksana::class, 'pelaksana', 'id');
    }

    /**
     * Get the status_users that owns the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status_users(): BelongsTo
    {
        return $this->belongsTo(StatusAktif::class, 'status_aktif', 'id');
    }
}
