<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class jabatan extends Model
{
    use HasFactory;
    protected $table = 'tb_jabatan';
    protected $with = ['pegawai','satuan_kerja','kelas_jabatan'];

    public function pegawai() {
        return $this->hasOne('App\Models\pegawai', 'id', 'id_pegawai');
    }

    public function kelompok_jabatan() {
        return $this->hasOne('App\Models\kelompok_jabatan', 'id', 'id_kelompok_jabatan');
    }

    public function jenis_jabatan() {
        return $this->hasOne('App\Models\jenis_jabatan', 'id', 'id_jenis_jabatan');
    }

    public function satuan_kerja() {
        return $this->hasOne('App\Models\satuan_kerja', 'id', 'id_satuan_kerja');
    }

    public function kelas_jabatan() {
        return $this->hasOne('App\Models\kelas_jabatan', 'id', 'id_kelas_jabatan');
    }
}
