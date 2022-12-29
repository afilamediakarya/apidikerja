<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableSkp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tb_skp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pegawai');
            $table->foreignId('id_satuan_kerja');
            $table->foreignId('id_skp_atasan');
            $table->enum('jenis', ['utama', 'tambahan']);
            $table->string('rencana_kerja');
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
        Schema::dropIfExists('table_skp');
    }
}
