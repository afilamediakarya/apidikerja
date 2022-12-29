<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableRealisasiSkp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tb_realisasi_skp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_aspek_skp');
            $table->integer('realisasi_bulanan');
            $table->enum('bulan', [1,2,3,4,5,6,7,8,9,10,11,12]);
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
        Schema::dropIfExists('table_realisasi_skp');
    }
}
