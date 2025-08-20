<?php

namespace App\Helpers;

use Carbon\Carbon;

class DateHelper
{
	// Fungsi untuk konversi dari Y-m-d ke d-m-Y
	public static function convertToDMY($date)
	{
		return Carbon::createFromFormat('Y-m-d', $date)
			->setTimezone('Asia/Jakarta')
			->format('d-m-Y');
	}

	// Fungsi untuk konversi dari Y-m-d ke d-mmmm-Y (contoh: 1 September 2024)
	public static function convertToFullDate($date)
	{
		return Carbon::createFromFormat('Y-m-d', $date)
			->setTimezone('Asia/Jakarta')
			->isoFormat('D MMMM YYYY');
	}

	// Fungsi untuk konversi dari Y-m-d ke mmmm-Y (contoh: September 2024)
	public static function convertToMonthYear($date)
	{
		return Carbon::createFromFormat('Y-m-d', $date)
			->setTimezone('Asia/Jakarta')
			->isoFormat('MMMM YYYY');
	}

	// Fungsi untuk konversi dari Y-m-d ke dddd, D MMMM YYYY (contoh: Senin, 1 September 2024)
	public static function convertToDayFullDate($date)
	{
		return Carbon::createFromFormat('Y-m-d', $date)
			->setTimezone('Asia/Jakarta') // Set timezone to Asia/Jakarta
			->isoFormat('dddd, D MMMM YYYY');
	}
}
