<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRiwayatJabatansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tb_riwayat_jabatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pegawai')->constrained('tb_pegawai')->onDelete('cascade');
            $table->foreignId('id_golongan')->constrained('tb_golongan')->onDelete('cascade');
            $table->foreignId('id_satuan_kerja')->constrained('tb_satuan_kerja')->onDelete('cascade');
            $table->enum('keterangan_jabatan', ['jabatan terbaru', 'plt', 'plh', 'pj', 'jabatan lama']);
            $table->enum('tipe_jabatan', ['administrasi', 'fungsional', 'pimpinan tinggi',]);
            $table->enum('jenis_jabatan', ['jabatan administrator', 'jabatan pengawas', 'jabatan pelaksana', 'ahli utama', 'ahli madya', 'ahli muda', 'ahli pertama', 'penyelia', 'mahir', 'terampil', 'pemula', 'jabatan pimpinan tinggi utama', 'jabatan pimpinan tinggi madya', 'jabatan pimpinan tinggi pratama']);
            $table->string('nama_jabatan', 20);
            $table->string('nomor_sk', 20);
            $table->date('tanggal_sk');
            $table->string('nama_pejabat', 20);
            $table->date('tmt');
            $table->string('document_jabatan', 255);
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
        Schema::dropIfExists('tb_riwayat_jabatan');
    }
}
