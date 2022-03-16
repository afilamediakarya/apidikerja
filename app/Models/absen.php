<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class absen extends Model
{
    use HasFactory;
    protected $table = 'tb_absen';
    protected $with = ['pegawai'];
    public function pegawai(){
        return $this->belongsTo('App\Models\pegawai','id_pegawai','id');
    }
}
