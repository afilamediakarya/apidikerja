<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableAktifitas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tb_aktivitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pegawai');
            $table->foreignId('id_skp');
            $table->string('nama_aktivitas');
            $table->string('keterangan');
            $table->string('satuan');
            $table->time('waktu_awal');
            $table->time('waktu_akhir');
            $table->date('tanggal');
            $table->string('tahun');
            $table->integer('hasil');
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
        Schema::dropIfExists('table_aktifitas');
    }
}
