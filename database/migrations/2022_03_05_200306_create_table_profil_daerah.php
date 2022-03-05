<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableProfilDaerah extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tb_profil_daerah', function (Blueprint $table) {
            $table->id();
            $table->string('nama_daerah');
            $table->string('pimpinan_daerah');
            $table->string('alamat');
            $table->string('email');
            $table->string('no_telp');
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
        Schema::dropIfExists('table_profil_daerah');
    }
}
