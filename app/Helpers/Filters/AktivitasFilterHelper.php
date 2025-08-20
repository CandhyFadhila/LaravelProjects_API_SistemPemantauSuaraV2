<?php

namespace App\Helpers\Filters;

use Illuminate\Database\Eloquent\Builder;

class AktivitasFilterHelper
{
	/**
	 * Menerapkan filter ke query Aktivitas berdasarkan request
	 *
	 * @param Builder $query
	 * @param array $filters
	 * @return Builder
	 */
	public static function applyFiltersAktivitas(Builder $query, array $filters): Builder
	{
		if (isset($filters['status_aktivitas'])) {
			$statusAktivitas = $filters['status_aktivitas'];
			$query->whereHas('status', function ($query) use ($statusAktivitas) {
				if (is_array($statusAktivitas)) {
					$query->whereIn('id', $statusAktivitas);
				} else {
					$query->where('id', '=', $statusAktivitas);
				}
			});
		}

		// Filter berdasarkan kode_kelurahan
		if (isset($filters['kode_kelurahan'])) {
			$kodeKelurahan = $filters['kode_kelurahan'];
			$query->whereHas('kelurahans', function ($query) use ($kodeKelurahan) {
				$query->whereIn('kode_kelurahan', $kodeKelurahan);
			});
		}

		if (isset($filters['search']) && is_array($filters['search'])) {
			$searchTerms = $filters['search'];
			$query->where(function ($query) use ($searchTerms) {
				foreach ($searchTerms as $term) {
					$searchTerm = '%' . $term . '%';
					$query->orWhereHas('pelaksana_users', function ($query) use ($searchTerm) {
						$query->where('nama', 'like', $searchTerm);
					});
				}
			});
		}

		return $query;
	}
}
