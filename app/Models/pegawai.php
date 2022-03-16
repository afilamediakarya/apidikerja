<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class pegawai extends Model
{
    use HasFactory;
    protected $table = 'tb_pegawai';
    protected $with = ['satuan_kerja'];
    public function satuan_kerja(){
        return $this->hasMany('App\Models\satuan_kerja','id','id_satuan_kerja');
    }

    public function skp(){
        return $this->belongsTo('App\Models\skp','id','id_pegawai');
    }

    public function bidang(){
          return $this->belongsTo('App\Models\pegawai','id','id_kepala_bidang');
    }

    public function atasan_penilai(){
        return $this->hasOne('App\Models\atasan','id','id_penilai');
    }

    public function atasan_pegawai(){
        return $this->hasOne('App\Models\atasan','id','id_pegawai');
    }

    public function absen(){
        return $this->hasOne('App\Models\absen','id','id_pegawai');
    }


}