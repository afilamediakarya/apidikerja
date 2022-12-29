<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRiwayatKepangkatansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tb_riwayat_kepangkatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pegawai')->constrained('tb_pegawai')->onDelete('cascade');
            $table->foreignId('id_golongan')->constrained('tb_golongan')->onDelete('cascade');
            $table->foreignId('id_satuan_kerja')->constrained('tb_satuan_kerja')->onDelete('cascade');
            $table->enum('jenis_kenaikan_pangkat', ['reguler', 'jabatan struktural', 'jabatan fungsional', 'penyesuaian ijzah']);
            $table->string('tahun_kerja', 10);
            $table->string('bulan_kerja', 10);
            $table->string('nomor_nota', 10);
            $table->date('tanggal_nota');
            $table->bigInteger('gaji_pokok');
            $table->string('nomor_sk', 20);
            $table->date('tanggal_sk');
            $table->date('tmt');
            $table->string('nama_pejabat', 20);
            $table->string('document_kepangkatan', 255);
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
        Schema::dropIfExists('tb_riwayat_kepangkatan');
    }
}
