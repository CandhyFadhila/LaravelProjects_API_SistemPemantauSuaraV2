<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class RandomHelper
{
	public static function generatePasswordRandom(int $length = 12): string
	{
		$chars = Str::random($length);
		$passwordRandom = "";
		for ($i = 0; $i < $length; $i++) {
			$passwordRandom .= $chars[random_int(0, strlen($chars) - 1)];
		}

		return $passwordRandom;
	}

	public static function generatePasswordBasic(): string
	{
		$passwordBasic = "bocahe_dewe";
		return $passwordBasic;
	}

	public static function generateUsername($nama): string
	{
		$nama = strtolower($nama);
		$namaArray = explode(' ', $nama);
		if (count($namaArray) > 1) {
			$namaDepan = $namaArray[0];
			$namaBelakang = $namaArray[count($namaArray) - 1];
			return $namaDepan . '.' . $namaBelakang;
		} else {
			return $namaArray[0];
		}
	}
}
