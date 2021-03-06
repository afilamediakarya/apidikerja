<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableReviewRealisasiSkp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tb_review_realisasi_skp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_skp');
            $table->text('keterangan')->nullable();
            $table->enum('kesesuaian', ['ya', 'tidak']);
            $table->enum('bulan', [0,1,2,3,4,5,6,7,8,9,10,11,12]);
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
        Schema::dropIfExists('table_review_realisasi_skp');
    }
}
