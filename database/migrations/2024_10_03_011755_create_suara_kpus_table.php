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
            $table->text('alamat');
            $table->text('cakupan_wilayah')->nullable(); // import
            $table->foreignId('kategori_suara_id')->constrained('kategori_suaras');
            $table->integer('jumlah_suara');
            $table->integer('dpt_laki');
            $table->integer('dpt_perempuan');
            $table->integer('jumlah_dpt');
            $table->integer('suara_caleg')->nullable();
            $table->integer('suara_partai')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suara_kpus');
    }
};
