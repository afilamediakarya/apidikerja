<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class pegawai extends Model
{
    use HasFactory;
    protected $table = 'tb_pegawai';
    protected $with = ['satuan'];
    public function satuan(){
        return $this->hasMany('App\Models\satuan','id','id_satuan_kerja');
    }

}
