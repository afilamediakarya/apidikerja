<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class skp extends Model
{
    use HasFactory;
    protected $table = 'tb_skp';
    protected $with = ['pegawai','satuan_kerja'];

    public function pegawai(){
        return $this->hasMany('App\Models\pegawai','id','id_pegawai');
    }

    public function satuan_kerja(){
        return $this->hasMany('App\Models\satuan_kerja','id','id_pegawai');
    }

    public function aspek_skp(){
        return $this->hasOne('App\Models\aspek_skp','id','id_skp');
    }

    public function review_skp() {
        return $this->hasOne('App\Models\review_skp', 'id', 'id_skp');
    }

}
