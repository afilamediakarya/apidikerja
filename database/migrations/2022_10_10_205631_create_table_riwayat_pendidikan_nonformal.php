<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableRiwayatPendidikanNonformal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tb_riwayat_pendidikan_nonformal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pegawai')->constrained('tb_pegawai')->onDelete('cascade');
            $table->enum('jenis_pendidikan', ['formal', 'nonformal']);
            $table->string('nama_kursus', 50);
            $table->date('tanggal_mulai');
            $table->date('tanggal_akhir');
            $table->string('nomor_ijazah', 20);
            $table->date('tanggal_ijazah');
            $table->string('nama_pejabat', 20);
            $table->string('instansi_penyelenggara', 50);
            $table->string('tempat', 50);
            $table->string('document_nonformal', 255);
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
        Schema::dropIfExists('tb_riwayat_pendidikan_nonformal');
    }
}
