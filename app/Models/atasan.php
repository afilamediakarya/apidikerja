<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class atasan extends Model
{
    use HasFactory;
    protected $table = 'tb_atasan';
    protected $with = ['penilai','pegawai'];
    public function penilai(){
        return $this->belongsTo('App\Models\pegawai','id_penilai','id');
    }

    public function pegawai(){
        return $this->belongsTo('App\Models\pegawai','id_pegawai','id');
    }

}
