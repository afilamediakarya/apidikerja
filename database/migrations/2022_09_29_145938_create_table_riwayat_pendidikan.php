<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableRiwayatPendidikan extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tb_riwayat_pendidikan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pegawai')->constrained('tb_pegawai')->onDelete('cascade');
            $table->foreignId('id_pendidikan')->constrained('tb_pendidikan')->onDelete('cascade')->nullable();
            $table->enum('jenis_pendidikan', ['formal', 'non-formal']);
            $table->string('fakultas', 50);
            $table->string('jurusan', 50);
            $table->string('nomor_ijazah', 20);
            $table->date('tanggal_ijazah');
            $table->string('nama_kepala_sekolah', 20);
            $table->string('nama_sekolah', 50);
            $table->string('alamat_sekolah', 50);
            $table->string('document_formal', 255);
            $table->boolean('verifikasi')->default(0);
            $table->integer('id_pegawai_verifikator');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tb_riwayat_pendidikan');
    }
}
