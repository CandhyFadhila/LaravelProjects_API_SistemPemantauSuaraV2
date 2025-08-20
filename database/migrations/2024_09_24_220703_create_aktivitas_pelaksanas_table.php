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
        Schema::create('aktivitas_pelaksanas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pelaksana')->constrained('users');
            $table->foreignId('status_aktivitas')->constrained('status_aktivitas');
            $table->text('deskripsi')->nullable();
            $table->string('tgl_mulai');
            $table->string('tgl_selesai');
            $table->string('tempat_aktivitas');
            $table->string('foto_aktivitas')->nullable();
            $table->integer('rw');
            $table->integer('potensi_suara');
            $table->foreignId('kelurahan')->constrained('kelurahans');
            $table->foreignId('status_aktivitas_rw')->nullable()->constrained('status_aktivitas_rws'); // 1 = Alat Peraga, 2 = Sosialisasi
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aktivitas_pelaksanas');
    }
};
