<?php

namespace App\Helpers;

class StatusAktivitasHelper
{
	public static function DetermineStatusAktivitasKelurahan($list_rw)
	{
		$hasNull = in_array(null, $list_rw, true);
		$hasAlatPeraga = in_array(1, $list_rw);
		$allSosialisasi = count(array_filter($list_rw, fn($val) => $val === 2)) === count($list_rw);

		// Jika ada null dalam array
		if ($hasNull) {
			return [
				"id" => null,
				"label" => null,
				"color" => "F0F0F0",
				"created_at" => null,
				"updated_at" => null
			];
		}

		// Jika ada "Alat Peraga"
		if ($hasAlatPeraga) {
			return [
				"id" => 1,
				"label" => "Alat Peraga",
				"color" => "00CCFF",
				"created_at" => "2024-10-13T04:32:22.000000Z",
				"updated_at" => "2024-10-13T04:32:22.000000Z"
			];
		}

		// Jika semua status adalah "Sosialisasi"
		if ($allSosialisasi) {
			return [
				"id" => 2,
				"label" => "Sosialisasi",
				"color" => "0C6091",
				"created_at" => "2024-10-13T04:32:22.000000Z",
				"updated_at" => "2024-10-13T04:32:22.000000Z"
			];
		}

		// Default, return null status
		return [
			"id" => null,
			"label" => null,
			"color" => "FFFFFF",
			"created_at" => null,
			"updated_at" => null
		];
	}

	public static function TransformRwList($rwList)
	{
		return array_map(function ($rwStatus) {
			if ($rwStatus === 1) {
				return [
					"id" => 1,
					"label" => "Alat Peraga",
					"color" => "00CCFF",
					"created_at" => "2024-10-13T04:32:22.000000Z",
					"updated_at" => "2024-10-13T04:32:22.000000Z"
				];
			} elseif ($rwStatus === 2) {
				return [
					"id" => 2,
					"label" => "Sosialisasi",
					"color" => "0C6091",
					"created_at" => "2024-10-13T04:32:22.000000Z",
					"updated_at" => "2024-10-13T04:32:22.000000Z"
				];
			} else {
				return [
					"id" => null,
					"label" => null,
					"color" => "F0F0F0",
					"created_at" => null,
					"updated_at" => null
				];
			}
		}, $rwList);
	}
}
