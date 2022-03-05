<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTablePegawai extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tb_pegawai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_satuan_kerja');
            $table->string('nama');
            $table->string('tempat_tanggal_lahir');
            $table->string('nip');
            $table->string('golongan_pangkat');
            $table->string('tmt_golongan');
            $table->string('eselon');
            $table->enum('status_pegawai', ['Aktif', 'Tidak aktif']);
            $table->string('tmt_pegawai');
            $table->enum('jenis_kelamin', ['LAKI-LAKI', 'PEREMPUAN']);
            $table->string('agama');
            $table->enum('status_perkawinan', ['Menikah', 'Belum Menikah','Cerai hidup','Cerai mati']);
            $table->enum('pendidikan_akhir', ['SLTA/Sederajat', 'Diploma I/II','Akademi/Diploma III/Sarjana Muda','Diploma IV/Strata I','Strata II','Strata III']);
            $table->string('no_npwp');
            $table->string('no_ktp');
            $table->string('alamat_rumah');
            $table->string('email');
            $table->string('jenis_jabatan');
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
        Schema::dropIfExists('table_pegawai');
    }
}
