<?php

namespace App\Traits;

use App\Helpers\VersionedCacheHelper;
use Illuminate\Support\Facades\DB;

trait BumpsCacheVersion
{
  protected static string $cacheNamespace = 'default';

  protected static function bootBumpsCacheVersion(): void
  {
    static::created(function () {
      DB::afterCommit(function () {
        VersionedCacheHelper::bump(static::$cacheNamespace);
      });
    });

    static::updated(function () {
      DB::afterCommit(function () {
        VersionedCacheHelper::bump(static::$cacheNamespace);
      });
    });

    static::deleted(function () {
      DB::afterCommit(function () {
        VersionedCacheHelper::bump(static::$cacheNamespace);
      });
    });

    // Jika pakai SoftDeletes dan ingin invalidate saat restore:
    if (method_exists(static::class, 'bootSoftDeletes')) {
      static::restored(function () {
        DB::afterCommit(function () {
          VersionedCacheHelper::bump(static::$cacheNamespace);
        });
      });
    }
  }
}
