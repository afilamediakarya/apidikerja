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

    public function bidang(){
          return $this->belongsTo('App\Models\pegawai','id','id_kepala_bidang');
        // return $this->hasMany('App\Models\bidang','id','id_kepala_bidang');
    }

}
