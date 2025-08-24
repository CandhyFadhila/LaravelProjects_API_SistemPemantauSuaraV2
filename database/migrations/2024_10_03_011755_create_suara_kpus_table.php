<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('suara_kpus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partai_id')->constrained('partais');
            $table->foreignId('kelurahan_id')->constrained('kelurahans');
            $table->integer('tahun');
            $table->integer('tps');
            $table->text('alamat')->nullable();
            $table->text('cakupan_wilayah')->nullable(); // import
            $table->foreignId('kategori_suara_id')->constrained('kategori_suaras');
            $table->integer('jumlah_suara');
            $table->integer('dpt_laki')->default(0);
            $table->integer('dpt_perempuan')->default(0);
            $table->integer('jumlah_dpt')->default(0);
            $table->integer('suara_caleg')->default(0);
            $table->integer('suara_partai')->default(0);
            $table->timestamps();
        });

        // ALTER TABLE `suara_kpus` CHANGE `alamat` `alamat` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL, CHANGE `dpt_laki` `dpt_laki` INT NULL DEFAULT '0', CHANGE `dpt_perempuan` `dpt_perempuan` INT NULL DEFAULT '0', CHANGE `jumlah_dpt` `jumlah_dpt` INT NOT NULL DEFAULT '0', CHANGE `suara_caleg` `suara_caleg` INT NULL DEFAULT '0', CHANGE `suara_partai` `suara_partai` INT NULL DEFAULT '0';

        // INSERT INTO `partais` (`id`, `nama`, `color`, `created_at`, `updated_at`) VALUES (NULL, 'PASLON 01', 'FF0000', '2025-08-24 12:50:48', '2025-08-24 12:50:48'), (NULL, 'PASLON 02', '002060', '2025-08-24 12:50:48', '2025-08-24 12:50:48');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suara_kpus');
    }
};
