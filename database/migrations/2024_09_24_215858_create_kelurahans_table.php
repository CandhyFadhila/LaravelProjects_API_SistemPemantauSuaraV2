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
        Schema::create('kelurahans', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kelurahan');
            $table->string('kode_kelurahan');
            $table->integer('max_rw');
            $table->integer('jumlah_tps');
            $table->foreignId('provinsi_id')->constrained('provinsis');
            $table->foreignId('kabupaten_id')->constrained('kabupatens');
            $table->foreignId('kecamatan_id')->constrained('kecamatans');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelurahans');
    }
};
