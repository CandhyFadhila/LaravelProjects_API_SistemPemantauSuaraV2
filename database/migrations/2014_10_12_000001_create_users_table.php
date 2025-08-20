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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('username')->unique();
            $table->string('nik_ktp', 16);
            $table->string('foto_profil')->nullable();
            $table->string('no_hp', 50);
            $table->string('tgl_diangkat')->nullable();
            $table->boolean('jenis_kelamin'); // 1 = laki-laki, 0 = perempuan
            $table->foreignId('role_id')->nullable();
            $table->foreignId('status_aktif')->default(2)->constrained('status_aktifs'); // 1 = non aktif, 2 = aktif, 3 = dinonaktifkan
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->integer('pj_pelaksana')->nullable();
            $table->text('kelurahan_id')->nullable();
            $table->text('rw_pelaksana')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
