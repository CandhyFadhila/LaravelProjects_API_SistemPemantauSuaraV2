<?php

namespace App\Traits;

use App\Helpers\VersionedCacheHelper;
use Illuminate\Support\Facades\DB;
use ReflectionClass;

trait BumpsCacheVersion
{
  protected static function bootBumpsCacheVersion(): void
  {
    $bump = function () {
      VersionedCacheHelper::bump(static::cacheNamespace());
    };

    static::created(fn() => DB::afterCommit($bump));
    static::updated(fn() => DB::afterCommit($bump));
    static::deleted(fn() => DB::afterCommit($bump));

    // optional: untuk SoftDeletes
    if (in_array('Illuminate\\Database\\Eloquent\\SoftDeletes', class_uses_recursive(static::class), true)) {
      static::restored(fn() => DB::afterCommit($bump));
    }
  }

  protected static function cacheNamespace(): string
  {
    // 1) Prioritaskan constant: public const CACHE_NAMESPACE = '...';
    if (defined(static::class . '::CACHE_NAMESPACE')) {
      return constant(static::class . '::CACHE_NAMESPACE');
    }

    // 2) Fallback: baca properti via Reflection (tanpa akses langsung ke static::$...)
    $ref = new ReflectionClass(static::class);
    if ($ref->hasProperty('cacheNamespace')) {
      $prop = $ref->getProperty('cacheNamespace');
      if ($prop->isStatic()) {
        $prop->setAccessible(true);
        $val = $prop->getValue();
        if (is_string($val) && $val !== '') {
          return $val;
        }
      }
    }

    // 3) Default
    return 'default';
  }
}
