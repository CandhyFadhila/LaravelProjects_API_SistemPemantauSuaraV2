<?php

namespace App\Helpers;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class VersionedCacheHelper
{
  /**
   * Ambil+cache dengan key ber-versi (per namespace).
   * $parts berisi komponen pembeda (route, user, role, filters_hash, limit, dsb).
   */
  public static function remember(string $namespace, array $parts, Closure $callback, $ttl = null)
  {
    $key = self::key($namespace, $parts);
    $ttl = $ttl ?? now()->addMinutes(30);
    return Cache::remember($key, $ttl, $callback);
  }

  /**
   * Naikkan versi namespace untuk invalidasi massal.
   * Panggil saat CRUD (create/update/delete).
   */
  public static function bump(string $namespace, int $by = 1): int
  {
    $vk = self::versionKey($namespace);
    if (!Cache::has($vk)) {
      // Inisialisasi ke 1 agar increment valid
      Cache::forever($vk, 1);
      return 1;
    }
    return (int) Cache::increment($vk, $by);
  }

  /**
   * Bangun key versi-berbasis yang stabil & pendek.
   */
  public static function key(string $namespace, array $parts = []): string
  {
    $ver = (string) Cache::get(self::versionKey($namespace), 1);

    // Normalisasi & hash parts agar urutan stabil dan key pendek
    $normalized = self::normalizeParts($parts);
    $hash = substr(md5($normalized), 0, 20);

    return "vc:{$namespace}:v{$ver}:{$hash}";
  }

  /**
   * Utility: buat parts standar untuk list (dengan page).
   * - $filters akan di-sort recursive lalu di-hash agar konsisten.
   */
  public static function standardPagedParts(
    string $routeKey,
    int $userId = null,
    int $roleId = null,
    array $filters = [],
    int $page = 1,
    int $limit = 10
  ): array {
    $filters = Arr::sortRecursive($filters);
    return [
      'route'   => $routeKey,
      'user'    => $userId,
      'role'    => $roleId,
      'filters' => self::hashArray($filters),
      'page'    => $page,
      'limit'   => $limit,
    ];
  }

  /**
   * Utility: buat parts standar untuk list (tanpa page).
   * - $filters akan di-sort recursive lalu di-hash agar konsisten.
   */
  public static function standardParts(
    string $routeKey,
    int $userId = null,
    int $roleId = null,
    array $filters = [],
    int $limit = 10
  ): array {
    $filters = Arr::sortRecursive($filters);
    return [
      'route'   => $routeKey,
      'user'    => $userId,
      'role'    => $roleId,
      'filters' => self::hashArray($filters),
      'limit'   => $limit,
    ];
  }

  /**
   * Hash array (sorted) → string pendek.
   */
  public static function hashArray(array $value): string
  {
    return substr(md5(json_encode($value, JSON_UNESCAPED_UNICODE)), 0, 20);
  }

  private static function versionKey(string $namespace): string
  {
    return "vc:{$namespace}:ver";
  }

  private static function normalizeParts(array $parts): string
  {
    // Pastikan stabil & singkat
    $parts = Arr::sortRecursive($parts);

    array_walk($parts, function (&$v) {
      if (is_array($v)) {
        $v = self::hashArray($v);
      } elseif (is_object($v)) {
        $v = self::hashArray((array) $v);
      } elseif (is_bool($v)) {
        $v = $v ? '1' : '0';
      } elseif ($v === null) {
        $v = 'null';
      } else {
        $v = (string) $v;
      }
    });

    return implode('|', $parts);
  }
}
