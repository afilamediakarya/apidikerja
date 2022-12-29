<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableBidang extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tb_bidang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_kepala_bidang');
            $table->string('kode_bidang');
            $table->string('nama_bidang');
            $table->string('nama_jabatan_bidang');
            $table->string('tahun');
            $table->enum('status_kepala_bidang',['pejabat tetap','pejabat sementara']);
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
        Schema::dropIfExists('table_bidang');
    }
}
