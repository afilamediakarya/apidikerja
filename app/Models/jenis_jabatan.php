<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class jenis_jabatan extends Model
{
    use HasFactory;
    protected $table = 'tb_jenis_jabatan';

    public function jabatan() {
        return $this->belongsTo('App\Models\jabatan','id','id_jenis_jabatan');
    }

    public function kelompok_jabatan() {
        return $this->belongsTo('App\Models\kelompok_jabatan','id','id_jenis_jabatan');
    }
}
