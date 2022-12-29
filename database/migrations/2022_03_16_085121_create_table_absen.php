<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableAbsen extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tb_absen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pegawai');
            $table->date('tanggal_absen');
            $table->time('waktu_absen');
            $table->enum('status',['hadir','izin','sakit','luar','cuti','alpa']);
            $table->enum('jenis',['checkin','checkout']);
            $table->enum('location_auth',['valid','invalid']);
            $table->enum('face_auth',['valid','invalid']);
            $table->string('tahun');
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
        Schema::dropIfExists('table_absen');
    }
}
