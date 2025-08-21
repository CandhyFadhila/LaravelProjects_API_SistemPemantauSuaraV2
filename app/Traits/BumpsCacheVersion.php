<?php

namespace App\Traits;

use App\Helpers\VersionedCacheHelper;

trait BumpsCacheVersion
{
  // Override di model: protected static string $cacheNamespace = 'aktivitas';
  protected static string $cacheNamespace = 'default';

  protected static function bootBumpsCacheVersion(): void
  {
    static::created(function () {
      VersionedCacheHelper::bump(static::$cacheNamespace);
    });
    static::updated(function () {
      VersionedCacheHelper::bump(static::$cacheNamespace);
    });
    static::deleted(function () {
      VersionedCacheHelper::bump(static::$cacheNamespace);
    });
  }
}
