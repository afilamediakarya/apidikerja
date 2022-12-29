<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class kelas_jabatan extends Model
{
    use HasFactory;
    protected $table = 'tb_kelas_jabatan';

    public function jabatan(){
        return $this->belongsTo('App\Models\jabatan','id_kelas_jabatan','id');
    }
}
