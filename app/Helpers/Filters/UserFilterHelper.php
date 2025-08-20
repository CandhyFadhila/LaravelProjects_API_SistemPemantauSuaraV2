<?php

namespace App\Helpers\Filters;

use Illuminate\Database\Eloquent\Builder;

class UserFilterHelper
{
	/**
	 * Menerapkan filter ke query User berdasarkan request
	 *
	 * @param Builder $query
	 * @param array $filters
	 * @return Builder
	 */
	public static function applyFiltersUser(Builder $query, array $filters): Builder
	{
		// Filter status aktif
		if (isset($filters['status_aktif'])) {
			$statusAktif = $filters['status_aktif'];
			$query->where('status_aktif', '=', $statusAktif);
		}

		// Filter tahun periode
		if (isset($filters['periode'])) {
			$periode = $filters['periode'];
			$query->whereYear('tgl_diangkat', '<=', $periode);
		}

		// Filter kelurahan berdasarkan kelurahan_id array
		if (isset($filters['kelurahan_id']) && is_array($filters['kelurahan_id'])) {
			$kelurahanIds = $filters['kelurahan_id'];
			$query->where(function ($query) use ($kelurahanIds) {
				foreach ($kelurahanIds as $kelurahanId) {
					// Gunakan JSON_CONTAINS untuk memeriksa kelurahan_id yang tersimpan sebagai array JSON
					$query->orWhereRaw("JSON_CONTAINS(kelurahan_id, '\"$kelurahanId\"')");
				}
			});
		}

		// Filter pencarian
		if (isset($filters['search']) && is_array($filters['search'])) {
			$query->where(function ($query) use ($filters) {
				foreach ($filters['search'] as $term) {
					$query->orWhere('nama', 'like', '%' . $term . '%');
				}
			});
		}

		return $query;
	}
}
