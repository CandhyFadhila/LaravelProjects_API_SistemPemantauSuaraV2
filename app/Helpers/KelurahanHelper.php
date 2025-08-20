<?php

namespace App\Helpers;

class KelurahanHelper
{
	/**
	 * Mendapatkan daftar kelurahan beserta kecamatan_id-nya.
	 *
	 * @return array
	 */
	public static function getKelurahanWithKecamatanId() 
	{
		return [
			// Kecamatan Mijen
			['nama_kelurahan' => 'Bubakan', 'kecamatan_id' => 1, 'kode_kelurahan' => 'id3374010002', 'max_rw' => 7],
			['nama_kelurahan' => 'Cangkiran', 'kecamatan_id' => 1, 'kode_kelurahan' => 'id3374010001', 'max_rw' => 12],
			['nama_kelurahan' => 'Jatibarang', 'kecamatan_id' => 1, 'kode_kelurahan' => 'id3374010009', 'max_rw' => 16],
			['nama_kelurahan' => 'Jatisari', 'kecamatan_id' => 1, 'kode_kelurahan' => 'id3374010007', 'max_rw' => 10],
			['nama_kelurahan' => 'Karangmalang', 'kecamatan_id' => 1, 'kode_kelurahan' => 'id3374010003', 'max_rw' => 13],
			['nama_kelurahan' => 'Kedungpani', 'kecamatan_id' => 1, 'kode_kelurahan' => 'id3374010010', 'max_rw' => 9],
			['nama_kelurahan' => 'Mijen', 'kecamatan_id' => 1, 'kode_kelurahan' => 'id3374010008', 'max_rw' => 12],
			['nama_kelurahan' => 'Ngadirgo', 'kecamatan_id' => 1, 'kode_kelurahan' => 'id3374010012', 'max_rw' => 14],
			['nama_kelurahan' => 'Pesantren', 'kecamatan_id' => 1, 'kode_kelurahan' => 'id3374010011', 'max_rw' => 7],
			['nama_kelurahan' => 'Polaman', 'kecamatan_id' => 1, 'kode_kelurahan' => 'id3374010004', 'max_rw' => 10],
			['nama_kelurahan' => 'Purwosari', 'kecamatan_id' => 1, 'kode_kelurahan' => 'id3374010005', 'max_rw' => 11],
			['nama_kelurahan' => 'Tambangan', 'kecamatan_id' => 1, 'kode_kelurahan' => 'id3374010006', 'max_rw' => 12],
			['nama_kelurahan' => 'Wonolopo', 'kecamatan_id' => 1, 'kode_kelurahan' => 'id3374010013', 'max_rw' => 12],
			['nama_kelurahan' => 'Wonoplumbon', 'kecamatan_id' => 1, 'kode_kelurahan' => 'id3374010014', 'max_rw' => 8],


			// Kecamatan Gunung Pati
			['nama_kelurahan' => 'Cepoko', 'kecamatan_id' => 2, 'kode_kelurahan' => 'id3374020009', 'max_rw' => 10],
			['nama_kelurahan' => 'Gunungpati', 'kecamatan_id' => 2, 'kode_kelurahan' => 'id3374020001', 'max_rw' => 12],
			['nama_kelurahan' => 'Jatirejo', 'kecamatan_id' => 2, 'kode_kelurahan' => 'id3374020010', 'max_rw' => 9],
			['nama_kelurahan' => 'Kalisegoro', 'kecamatan_id' => 2, 'kode_kelurahan' => 'id3374020013', 'max_rw' => 8],
			['nama_kelurahan' => 'Mangunsari', 'kecamatan_id' => 2, 'kode_kelurahan' => 'id3374020005', 'max_rw' => 13],
			['nama_kelurahan' => 'Ngijo', 'kecamatan_id' => 2, 'kode_kelurahan' => 'id3374020007', 'max_rw' => 10],
			['nama_kelurahan' => 'Nongkosawit', 'kecamatan_id' => 2, 'kode_kelurahan' => 'id3374020008', 'max_rw' => 7],
			['nama_kelurahan' => 'Pakintelan', 'kecamatan_id' => 2, 'kode_kelurahan' => 'id3374020004', 'max_rw' => 10],
			['nama_kelurahan' => 'Patemon', 'kecamatan_id' => 2, 'kode_kelurahan' => 'id3374020006', 'max_rw' => 9],
			['nama_kelurahan' => 'Plalangan', 'kecamatan_id' => 2, 'kode_kelurahan' => 'id3374020002', 'max_rw' => 11],
			['nama_kelurahan' => 'Pungangan', 'kecamatan_id' => 2, 'kode_kelurahan' => 'id3374020012', 'max_rw' => 7],
			['nama_kelurahan' => 'Sadeng', 'kecamatan_id' => 2, 'kode_kelurahan' => 'id3374020016', 'max_rw' => 12],
			['nama_kelurahan' => 'Sekaran', 'kecamatan_id' => 2, 'kode_kelurahan' => 'id3374020014', 'max_rw' => 10],
			['nama_kelurahan' => 'Sukorejo', 'kecamatan_id' => 2, 'kode_kelurahan' => 'id3374020015', 'max_rw' => 9],
			['nama_kelurahan' => 'Kandri', 'kecamatan_id' => 2, 'kode_kelurahan' => 'id3374020011', 'max_rw' => 10],
			['nama_kelurahan' => 'Sumurejo', 'kecamatan_id' => 2, 'kode_kelurahan' => 'id3374020003', 'max_rw' => 8],


			// Banyumanik
			['nama_kelurahan' => 'Banyumanik', 'kecamatan_id' => 3, 'kode_kelurahan' => 'id3374030005', 'max_rw' => 13],
			['nama_kelurahan' => 'Gedawang', 'kecamatan_id' => 3, 'kode_kelurahan' => 'id3374030002', 'max_rw' => 9],
			['nama_kelurahan' => 'Jabungan', 'kecamatan_id' => 3, 'kode_kelurahan' => 'id3374030003', 'max_rw' => 10],
			['nama_kelurahan' => 'Ngesrep', 'kecamatan_id' => 3, 'kode_kelurahan' => 'id3374030011', 'max_rw' => 12],
			['nama_kelurahan' => 'Padangsari', 'kecamatan_id' => 3, 'kode_kelurahan' => 'id3374030004', 'max_rw' => 11],
			['nama_kelurahan' => 'Pedalangan', 'kecamatan_id' => 3, 'kode_kelurahan' => 'id3374030007', 'max_rw' => 14],
			['nama_kelurahan' => 'Pudakpayung', 'kecamatan_id' => 3, 'kode_kelurahan' => 'id3374030001', 'max_rw' => 12],
			['nama_kelurahan' => 'Srondol Kulon', 'kecamatan_id' => 3, 'kode_kelurahan' => 'id3374030009', 'max_rw' => 10],
			['nama_kelurahan' => 'Srondol Wetan', 'kecamatan_id' => 3, 'kode_kelurahan' => 'id3374030006', 'max_rw' => 9],
			['nama_kelurahan' => 'Sumurboto', 'kecamatan_id' => 3, 'kode_kelurahan' => 'id3374030008', 'max_rw' => 11],
			['nama_kelurahan' => 'Tinjomoyo', 'kecamatan_id' => 3, 'kode_kelurahan' => 'id3374030010', 'max_rw' => 8],

			// Gajah Mungkur
			['nama_kelurahan' => 'Bendan Duwur', 'kecamatan_id' => 4, 'kode_kelurahan' => 'id3374040002', 'max_rw' => 9],
			['nama_kelurahan' => 'Bendan Ngisor', 'kecamatan_id' => 4, 'kode_kelurahan' => 'id3374040005', 'max_rw' => 11],
			['nama_kelurahan' => 'Bendungan', 'kecamatan_id' => 4, 'kode_kelurahan' => 'id3374040007', 'max_rw' => 10],
			['nama_kelurahan' => 'Gajahmungkur', 'kecamatan_id' => 4, 'kode_kelurahan' => 'id3374040004', 'max_rw' => 12],
			['nama_kelurahan' => 'Karang Rejo', 'kecamatan_id' => 4, 'kode_kelurahan' => 'id3374040003', 'max_rw' => 10],
			['nama_kelurahan' => 'Lempongsari', 'kecamatan_id' => 4, 'kode_kelurahan' => 'id3374040008', 'max_rw' => 9],
			['nama_kelurahan' => 'Petompon', 'kecamatan_id' => 4, 'kode_kelurahan' => 'id3374040006', 'max_rw' => 8],
			['nama_kelurahan' => 'Sampangan', 'kecamatan_id' => 4, 'kode_kelurahan' => 'id3374040001', 'max_rw' => 13],

			// Semarang Selatan
			['nama_kelurahan' => 'Barusari', 'kecamatan_id' => 5, 'kode_kelurahan' => 'id3374050002', 'max_rw' => 12],
			['nama_kelurahan' => 'Bulustalan', 'kecamatan_id' => 5, 'kode_kelurahan' => 'id3374050001', 'max_rw' => 9],
			['nama_kelurahan' => 'Lamper Kidul', 'kecamatan_id' => 5, 'kode_kelurahan' => 'id3374050008', 'max_rw' => 10],
			['nama_kelurahan' => 'Lamper Lor', 'kecamatan_id' => 5, 'kode_kelurahan' => 'id3374050009', 'max_rw' => 11],
			['nama_kelurahan' => 'Lamper Tengah', 'kecamatan_id' => 5, 'kode_kelurahan' => 'id3374050010', 'max_rw' => 10],
			['nama_kelurahan' => 'Mugassari', 'kecamatan_id' => 5, 'kode_kelurahan' => 'id3374050004', 'max_rw' => 13],
			['nama_kelurahan' => 'Peterongan', 'kecamatan_id' => 5, 'kode_kelurahan' => 'id3374050007', 'max_rw' => 12],
			['nama_kelurahan' => 'Pleburan', 'kecamatan_id' => 5, 'kode_kelurahan' => 'id3374050005', 'max_rw' => 8],
			['nama_kelurahan' => 'Randusari', 'kecamatan_id' => 5, 'kode_kelurahan' => 'id3374050003', 'max_rw' => 9],
			['nama_kelurahan' => 'Wonodri', 'kecamatan_id' => 5, 'kode_kelurahan' => 'id3374050006', 'max_rw' => 10],

			// Candisari
			['nama_kelurahan' => 'Candi', 'kecamatan_id' => 6, 'kode_kelurahan' => 'id3374060004', 'max_rw' => 10],
			['nama_kelurahan' => 'Jatingaleh', 'kecamatan_id' => 6, 'kode_kelurahan' => 'id3374060001', 'max_rw' => 12],
			['nama_kelurahan' => 'Jomblang', 'kecamatan_id' => 6, 'kode_kelurahan' => 'id3374060003', 'max_rw' => 11],
			['nama_kelurahan' => 'Kaliwiru', 'kecamatan_id' => 6, 'kode_kelurahan' => 'id3374060005', 'max_rw' => 9],
			['nama_kelurahan' => 'Karanganyar Gunung', 'kecamatan_id' => 6, 'kode_kelurahan' => 'id3374060002', 'max_rw' => 8],
			['nama_kelurahan' => 'Tegalsari', 'kecamatan_id' => 6, 'kode_kelurahan' => 'id3374060007', 'max_rw' => 10],
			['nama_kelurahan' => 'Wonotingal', 'kecamatan_id' => 6, 'kode_kelurahan' => 'id3374060006', 'max_rw' => 7],

			// Tembalang
			['nama_kelurahan' => 'Tembalang', 'kecamatan_id' => 7, 'kode_kelurahan' => 'id3374070004', 'max_rw' => 12],
			['nama_kelurahan' => 'Bulusan', 'kecamatan_id' => 7, 'kode_kelurahan' => 'id3374070005', 'max_rw' => 9],
			['nama_kelurahan' => 'Jangli', 'kecamatan_id' => 7, 'kode_kelurahan' => 'id3374070009', 'max_rw' => 10],
			['nama_kelurahan' => 'Kedungmundu', 'kecamatan_id' => 7, 'kode_kelurahan' => 'id3374070011', 'max_rw' => 11],
			['nama_kelurahan' => 'Kramas', 'kecamatan_id' => 7, 'kode_kelurahan' => 'id3374070003', 'max_rw' => 8],
			['nama_kelurahan' => 'Mangunharjo', 'kecamatan_id' => 7, 'kode_kelurahan' => 'id3374070006', 'max_rw' => 9],
			['nama_kelurahan' => 'Meteseh', 'kecamatan_id' => 7, 'kode_kelurahan' => 'id3374070002', 'max_rw' => 13],
			['nama_kelurahan' => 'Rowosari', 'kecamatan_id' => 7, 'kode_kelurahan' => 'id3374070001', 'max_rw' => 10],
			['nama_kelurahan' => 'Sambiroto', 'kecamatan_id' => 7, 'kode_kelurahan' => 'id3374070008', 'max_rw' => 12],
			['nama_kelurahan' => 'Sendangguwo', 'kecamatan_id' => 7, 'kode_kelurahan' => 'id3374070012', 'max_rw' => 10],
			['nama_kelurahan' => 'Sendangmulyo', 'kecamatan_id' => 7, 'kode_kelurahan' => 'id3374070007', 'max_rw' => 15],
			['nama_kelurahan' => 'Tandang', 'kecamatan_id' => 7, 'kode_kelurahan' => 'id3374070010', 'max_rw' => 11],

			// Pedurungan
			['nama_kelurahan' => 'Pedurungan Kidul', 'kecamatan_id' => 8, 'kode_kelurahan' => 'id3374080002', 'max_rw' => 12],
			['nama_kelurahan' => 'Pedurungan Lor', 'kecamatan_id' => 8, 'kode_kelurahan' => 'id3374080005', 'max_rw' => 10],
			['nama_kelurahan' => 'Pedurungan Tengah', 'kecamatan_id' => 8, 'kode_kelurahan' => 'id3374080007', 'max_rw' => 9],
			['nama_kelurahan' => 'Gemah', 'kecamatan_id' => 8, 'kode_kelurahan' => 'id3374080001', 'max_rw' => 8],
			['nama_kelurahan' => 'Kalicari', 'kecamatan_id' => 8, 'kode_kelurahan' => 'id3374080009', 'max_rw' => 11],
			['nama_kelurahan' => 'Muktiharjo Kidul', 'kecamatan_id' => 8, 'kode_kelurahan' => 'id3374080012', 'max_rw' => 13],
			['nama_kelurahan' => 'Palebon', 'kecamatan_id' => 8, 'kode_kelurahan' => 'id3374080008', 'max_rw' => 12],
			['nama_kelurahan' => 'Penggaron Kidul', 'kecamatan_id' => 8, 'kode_kelurahan' => 'id3374080004', 'max_rw' => 10],
			['nama_kelurahan' => 'Plamongan Sari', 'kecamatan_id' => 8, 'kode_kelurahan' => 'id3374080003', 'max_rw' => 9],
			['nama_kelurahan' => 'Tlogomulyo', 'kecamatan_id' => 8, 'kode_kelurahan' => 'id3374080006', 'max_rw' => 8],
			['nama_kelurahan' => 'Tlogosari Kulon', 'kecamatan_id' => 8, 'kode_kelurahan' => 'id3374080010', 'max_rw' => 14],
			['nama_kelurahan' => 'Tlogosari Wetan', 'kecamatan_id' => 8, 'kode_kelurahan' => 'id3374080011', 'max_rw' => 15],

			// Genuk
			['nama_kelurahan' => 'Bangetayu Kulon', 'kecamatan_id' => 9, 'kode_kelurahan' => 'id3374090004', 'max_rw' => 10],
			['nama_kelurahan' => 'Bangetayu Wetan', 'kecamatan_id' => 9, 'kode_kelurahan' => 'id3374090005', 'max_rw' => 9],
			['nama_kelurahan' => 'Banjardowo', 'kecamatan_id' => 9, 'kode_kelurahan' => 'id3374090010', 'max_rw' => 12],
			['nama_kelurahan' => 'Gebangsari', 'kecamatan_id' => 9, 'kode_kelurahan' => 'id3374090002', 'max_rw' => 11],
			['nama_kelurahan' => 'Genuksari', 'kecamatan_id' => 9, 'kode_kelurahan' => 'id3374090003', 'max_rw' => 8],
			['nama_kelurahan' => 'Karangroto', 'kecamatan_id' => 9, 'kode_kelurahan' => 'id3374090009', 'max_rw' => 10],
			['nama_kelurahan' => 'Kudu', 'kecamatan_id' => 9, 'kode_kelurahan' => 'id3374090008', 'max_rw' => 13],
			['nama_kelurahan' => 'Muktiharjo Lor', 'kecamatan_id' => 9, 'kode_kelurahan' => 'id3374090001', 'max_rw' => 15],
			['nama_kelurahan' => 'Penggaron Lor', 'kecamatan_id' => 9, 'kode_kelurahan' => 'id3374090007', 'max_rw' => 9],
			['nama_kelurahan' => 'Sembungharjo', 'kecamatan_id' => 9, 'kode_kelurahan' => 'id3374090006', 'max_rw' => 10],
			['nama_kelurahan' => 'Terboyo Kulon', 'kecamatan_id' => 9, 'kode_kelurahan' => 'id3374090013', 'max_rw' => 12],
			['nama_kelurahan' => 'Terboyo Wetan', 'kecamatan_id' => 9, 'kode_kelurahan' => 'id3374090012', 'max_rw' => 8],
			['nama_kelurahan' => 'Trimulyo', 'kecamatan_id' => 9, 'kode_kelurahan' => 'id3374090011', 'max_rw' => 11],

			// Gayamsari
			['nama_kelurahan' => 'Gayamsari', 'kecamatan_id' => 10, 'kode_kelurahan' => 'id3374100002', 'max_rw' => 11],
			['nama_kelurahan' => 'Kaligawe', 'kecamatan_id' => 10, 'kode_kelurahan' => 'id3374100006', 'max_rw' => 10],
			['nama_kelurahan' => 'Pandean Lamper', 'kecamatan_id' => 10, 'kode_kelurahan' => 'id3374100001', 'max_rw' => 12],
			['nama_kelurahan' => 'Sambirejo', 'kecamatan_id' => 10, 'kode_kelurahan' => 'id3374100004', 'max_rw' => 9],
			['nama_kelurahan' => 'Sawahbesar', 'kecamatan_id' => 10, 'kode_kelurahan' => 'id3374100005', 'max_rw' => 8],
			['nama_kelurahan' => 'Siwalan', 'kecamatan_id' => 10, 'kode_kelurahan' => 'id3374100003', 'max_rw' => 11],
			['nama_kelurahan' => 'Tambakrejo', 'kecamatan_id' => 10, 'kode_kelurahan' => 'id3374100007', 'max_rw' => 10],

			// Semarang Timur
			['nama_kelurahan' => 'Bugangan', 'kecamatan_id' => 11, 'kode_kelurahan' => 'id3374110006', 'max_rw' => 11],
			['nama_kelurahan' => 'Karangtempel', 'kecamatan_id' => 11, 'kode_kelurahan' => 'id3374110002', 'max_rw' => 9],
			['nama_kelurahan' => 'Karangturi', 'kecamatan_id' => 11, 'kode_kelurahan' => 'id3374110001', 'max_rw' => 10],
			['nama_kelurahan' => 'Kebonagung', 'kecamatan_id' => 11, 'kode_kelurahan' => 'id3374110005', 'max_rw' => 8],
			['nama_kelurahan' => 'Kemijen', 'kecamatan_id' => 11, 'kode_kelurahan' => 'id3374110010', 'max_rw' => 12],
			['nama_kelurahan' => 'Mlatibaru', 'kecamatan_id' => 11, 'kode_kelurahan' => 'id3374110008', 'max_rw' => 9],
			['nama_kelurahan' => 'Mlatiharjo', 'kecamatan_id' => 11, 'kode_kelurahan' => 'id3374110007', 'max_rw' => 8],
			['nama_kelurahan' => 'Rejomulyo', 'kecamatan_id' => 11, 'kode_kelurahan' => 'id3374110009', 'max_rw' => 10],
			['nama_kelurahan' => 'Rejosari', 'kecamatan_id' => 11, 'kode_kelurahan' => 'id3374110003', 'max_rw' => 11],
			['nama_kelurahan' => 'Sarirejo', 'kecamatan_id' => 11, 'kode_kelurahan' => 'id3374110004', 'max_rw' => 9],

			// Semarang Utara
			['nama_kelurahan' => 'Bandarharjo', 'kecamatan_id' => 12, 'kode_kelurahan' => 'id3374120008', 'max_rw' => 12],
			['nama_kelurahan' => 'Bulu Lor', 'kecamatan_id' => 12, 'kode_kelurahan' => 'id3374120001', 'max_rw' => 10],
			['nama_kelurahan' => 'Dadapsari', 'kecamatan_id' => 12, 'kode_kelurahan' => 'id3374120007', 'max_rw' => 9],
			['nama_kelurahan' => 'Kuningan', 'kecamatan_id' => 12, 'kode_kelurahan' => 'id3374120005', 'max_rw' => 8],
			['nama_kelurahan' => 'Panggung Kidul', 'kecamatan_id' => 12, 'kode_kelurahan' => 'id3374120003', 'max_rw' => 11],
			['nama_kelurahan' => 'Panggung Lor', 'kecamatan_id' => 12, 'kode_kelurahan' => 'id3374120004', 'max_rw' => 9],
			['nama_kelurahan' => 'Plombokan', 'kecamatan_id' => 12, 'kode_kelurahan' => 'id3374120002', 'max_rw' => 10],
			['nama_kelurahan' => 'Purwosari', 'kecamatan_id' => 12, 'kode_kelurahan' => 'id3374120006', 'max_rw' => 11],
			['nama_kelurahan' => 'Tanjungmas', 'kecamatan_id' => 12, 'kode_kelurahan' => 'id3374120009', 'max_rw' => 13],

			// Semarang Tengah
			['nama_kelurahan' => 'Bangunharjo', 'kecamatan_id' => 13, 'kode_kelurahan' => 'id3374130010', 'max_rw' => 10],
			['nama_kelurahan' => 'Brumbungan', 'kecamatan_id' => 13, 'kode_kelurahan' => 'id3374130004', 'max_rw' => 9],
			['nama_kelurahan' => 'Gabahan', 'kecamatan_id' => 13, 'kode_kelurahan' => 'id3374130006', 'max_rw' => 8],
			['nama_kelurahan' => 'Jagalan', 'kecamatan_id' => 13, 'kode_kelurahan' => 'id3374130003', 'max_rw' => 7],
			['nama_kelurahan' => 'Karangkidul', 'kecamatan_id' => 13, 'kode_kelurahan' => 'id3374130002', 'max_rw' => 9],
			['nama_kelurahan' => 'Kauman', 'kecamatan_id' => 13, 'kode_kelurahan' => 'id3374130009', 'max_rw' => 12],
			['nama_kelurahan' => 'Kembangsari', 'kecamatan_id' => 13, 'kode_kelurahan' => 'id3374130011', 'max_rw' => 11],
			['nama_kelurahan' => 'Kranggan', 'kecamatan_id' => 13, 'kode_kelurahan' => 'id3374130007', 'max_rw' => 10],
			['nama_kelurahan' => 'Miroto', 'kecamatan_id' => 13, 'kode_kelurahan' => 'id3374130005', 'max_rw' => 8],
			['nama_kelurahan' => 'Pandansari', 'kecamatan_id' => 13, 'kode_kelurahan' => 'id3374130012', 'max_rw' => 10],
			['nama_kelurahan' => 'Pekunden', 'kecamatan_id' => 13, 'kode_kelurahan' => 'id3374130001', 'max_rw' => 9],
			['nama_kelurahan' => 'Pendrikan Kidul', 'kecamatan_id' => 13, 'kode_kelurahan' => 'id3374130014', 'max_rw' => 8],
			['nama_kelurahan' => 'Pendrikan Lor', 'kecamatan_id' => 13, 'kode_kelurahan' => 'id3374130015', 'max_rw' => 9],
			['nama_kelurahan' => 'Purwodinatan', 'kecamatan_id' => 13, 'kode_kelurahan' => 'id3374130008', 'max_rw' => 7],
			['nama_kelurahan' => 'Sekayu', 'kecamatan_id' => 13, 'kode_kelurahan' => 'id3374130013', 'max_rw' => 10],

			// Semarang Barat
			['nama_kelurahan' => 'Bojongsalaman', 'kecamatan_id' => 14, 'kode_kelurahan' => 'id3374140005', 'max_rw' => 12],
			['nama_kelurahan' => 'Bongsari', 'kecamatan_id' => 14, 'kode_kelurahan' => 'id3374140004', 'max_rw' => 10],
			['nama_kelurahan' => 'Cabean', 'kecamatan_id' => 14, 'kode_kelurahan' => 'id3374140006', 'max_rw' => 8],
			['nama_kelurahan' => 'Gisikdrono', 'kecamatan_id' => 14, 'kode_kelurahan' => 'id3374140008', 'max_rw' => 9],
			['nama_kelurahan' => 'Kalibanteng Kidul', 'kecamatan_id' => 14, 'kode_kelurahan' => 'id3374140009', 'max_rw' => 10],
			['nama_kelurahan' => 'Kalibanteng Kulon', 'kecamatan_id' => 14, 'kode_kelurahan' => 'id3374140010', 'max_rw' => 8],
			['nama_kelurahan' => 'Karang Ayu', 'kecamatan_id' => 14, 'kode_kelurahan' => 'id3374140014', 'max_rw' => 11],
			['nama_kelurahan' => 'Kembangarum', 'kecamatan_id' => 14, 'kode_kelurahan' => 'id3374140001', 'max_rw' => 9],
			['nama_kelurahan' => 'Krapyak', 'kecamatan_id' => 14, 'kode_kelurahan' => 'id3374140011', 'max_rw' => 12],
			['nama_kelurahan' => 'Krobokan', 'kecamatan_id' => 14, 'kode_kelurahan' => 'id3374140015', 'max_rw' => 9],
			['nama_kelurahan' => 'Manyaran', 'kecamatan_id' => 14, 'kode_kelurahan' => 'id3374140002', 'max_rw' => 8],
			['nama_kelurahan' => 'Ngemplaksimongan', 'kecamatan_id' => 14, 'kode_kelurahan' => 'id3374140003', 'max_rw' => 10],
			['nama_kelurahan' => 'Salamanmloyo', 'kecamatan_id' => 14, 'kode_kelurahan' => 'id3374140007', 'max_rw' => 11],
			['nama_kelurahan' => 'Tambak Harjo', 'kecamatan_id' => 14, 'kode_kelurahan' => 'id3374140012', 'max_rw' => 12],
			['nama_kelurahan' => 'Tawangmas', 'kecamatan_id' => 14, 'kode_kelurahan' => 'id3374140016', 'max_rw' => 10],
			['nama_kelurahan' => 'Tawangsari', 'kecamatan_id' => 14, 'kode_kelurahan' => 'id3374140013', 'max_rw' => 9],

			// Tugu
			['nama_kelurahan' => 'Jerakah', 'kecamatan_id' => 15, 'kode_kelurahan' => 'id3374150001', 'max_rw' => 11],
			['nama_kelurahan' => 'Karanganyar', 'kecamatan_id' => 15, 'kode_kelurahan' => 'id3374150003', 'max_rw' => 9],
			['nama_kelurahan' => 'Mangkang Kulon', 'kecamatan_id' => 15, 'kode_kelurahan' => 'id3374150007', 'max_rw' => 10],
			['nama_kelurahan' => 'Mangkang Wetan', 'kecamatan_id' => 15, 'kode_kelurahan' => 'id3374150005', 'max_rw' => 8],
			['nama_kelurahan' => 'Mangunharjo', 'kecamatan_id' => 15, 'kode_kelurahan' => 'id3374150006', 'max_rw' => 12],
			['nama_kelurahan' => 'Randu Garut', 'kecamatan_id' => 15, 'kode_kelurahan' => 'id3374150004', 'max_rw' => 9],
			['nama_kelurahan' => 'Tugurejo', 'kecamatan_id' => 15, 'kode_kelurahan' => 'id3374150002', 'max_rw' => 10],

			// Ngaliyan
			['nama_kelurahan' => 'Ngaliyan', 'kecamatan_id' => 16, 'kode_kelurahan' => 'id3374160004', 'max_rw' => 12],
			['nama_kelurahan' => 'Bambankerep', 'kecamatan_id' => 16, 'kode_kelurahan' => 'id3374160005', 'max_rw' => 9],
			['nama_kelurahan' => 'Bringin', 'kecamatan_id' => 16, 'kode_kelurahan' => 'id3374160003', 'max_rw' => 8],
			['nama_kelurahan' => 'Gondoriyo', 'kecamatan_id' => 16, 'kode_kelurahan' => 'id3374160009', 'max_rw' => 10],
			['nama_kelurahan' => 'Kalipancur', 'kecamatan_id' => 16, 'kode_kelurahan' => 'id3374160006', 'max_rw' => 11],
			['nama_kelurahan' => 'Podorejo', 'kecamatan_id' => 16, 'kode_kelurahan' => 'id3374160001', 'max_rw' => 9],
			['nama_kelurahan' => 'Purwoyoso', 'kecamatan_id' => 16, 'kode_kelurahan' => 'id3374160007', 'max_rw' => 12],
			['nama_kelurahan' => 'Tambakaji', 'kecamatan_id' => 16, 'kode_kelurahan' => 'id3374160008', 'max_rw' => 10],
			['nama_kelurahan' => 'Wates', 'kecamatan_id' => 16, 'kode_kelurahan' => 'id3374160002', 'max_rw' => 8],
			['nama_kelurahan' => 'Wonosari', 'kecamatan_id' => 16, 'kode_kelurahan' => 'id3374160010', 'max_rw' => 11],
		];
	}
}
