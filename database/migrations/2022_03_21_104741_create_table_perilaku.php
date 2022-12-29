<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTablePerilaku extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tb_perilaku', function (Blueprint $table) {
            $table->id();
            $table->text('perilaku');
            $table->text('definisi_perilaku');
            $table->text('kesimpulan_perilaku');
            $table->integer('number');
            $table->json('untuk');
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
        Schema::dropIfExists('table_perilaku');
    }
}
