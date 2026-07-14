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
        Schema::create('agendas', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->date('tanggal');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->string('lokasi');
            $table->text('deskripsi')->nullable();
            $table->string('kategori'); // 'rapat', 'kegiatan_umum'
            $table->json('hak_akses'); // ['semua_orang'] or [1, 2, 3] (array of bidang_id)
            $table->boolean('butuh_presensi')->default(false);
            $table->string('nomor_surat_dasar')->nullable();
            $table->foreignId('sekretaris_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agendas');
    }
};
