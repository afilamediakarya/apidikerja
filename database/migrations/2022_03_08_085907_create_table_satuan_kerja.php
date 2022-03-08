<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableSatuanKerja extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tb_satuan_kerja', function (Blueprint $table) {
            $table->id();
            $table->string('nama_satuan_kerja');
            $table->string('nama_jabatan_satuan_kerja');
            $table->string('kode_satuan_kerja');
            $table->string('lat_location');
            $table->string('long_location');
            $table->enum('status_kepala',['pejabat tetap','pejabat sementara']);
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
        Schema::dropIfExists('table_satuan_kerja');
    }
}
