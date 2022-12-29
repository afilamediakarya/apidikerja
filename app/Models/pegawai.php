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
        return $this->hasOne('App\Models\satuan_kerja','id','id_satuan_kerja');
    }

    public function skp(){
        return $this->hasMany('App\Models\skp','id_pegawai','id');
    }


    public function aktivitas(){
        return $this->hasMany('App\Models\aktivitas','id_pegawai','id');
    }


    public function user(){
        return $this->belongsTo('App\Models\User','id','id_pegawai');
    }

    public function bidang(){
          return $this->belongsTo('App\Models\pegawai','id','id_kepala_bidang');
    }

    public function jabatan(){
        return $this->belongsTo('App\Models\jabatan','id','id_pegawai');
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
