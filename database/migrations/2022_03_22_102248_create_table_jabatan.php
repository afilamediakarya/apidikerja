<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableJabatan extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tb_jabatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pegawai');
            $table->foreignId('id_satuan_kerja');
            $table->foreignId('id_kelas_jabatan');
            $table->foreignId('parent_id');
            $table->string('nama_struktur');
            $table->string('nama_jabatan');
            $table->enum('level',[1,2,3,4]);
            $table->enum('status_jabatan',['tetap','plt']);
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
        Schema::dropIfExists('table_jabatan');
    }
}
