<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class kelompok_jabatan extends Model
{
    use HasFactory;
    protected $table = 'tb_kelompok_jabatan';

    public function jenis_jabatan() {
        return $this->hasOne('App\Models\jenis_jabatan', 'id', 'id_jenis_jabatan');
    }

    public function masterAktivitas() {
        return $this->belongsTo('App\Models\masterAktivitas', 'id', 'id_kelompok_jabatan');
    }

    public function jabatan() {
        return $this->belongsTo('App\Models\jabatan', 'id', 'id_kelompok_jabatan');
    }


}
