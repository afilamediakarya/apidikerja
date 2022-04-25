<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class skp extends Model
{
    use HasFactory;
    protected $table = 'tb_skp';

    public function pegawai(){
        return $this->belongsTo('App\Models\pegawai','id_pegawai','id');
    }

    public function satuan_kerja(){
        return $this->hasMany('App\Models\satuan_kerja','id','id_pegawai');
    }

    public function aspek_skp(){
        return $this->hasMany('App\Models\aspek_skp','id_skp','id');
    }

    public function aktivitas(){
        return $this->belongsTo('App\Models\aktivitas','id_skp','id');
    }


    public function review_skp() {
        return $this->hasOne('App\Models\review_skp','id_skp' ,'id');
    }

}
