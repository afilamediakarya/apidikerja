<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class bidang extends Model
{
    use HasFactory;
    protected $table = 'tb_bidang';
    protected $with = ['pegawai'];
    public function pegawai(){
          return $this->hasMany('App\Models\pegawai','id','id_kepala_bidang');
        // return $this->belongsTo('App\Models\pegawai','id_kepala_bidang','id');
    }
}
