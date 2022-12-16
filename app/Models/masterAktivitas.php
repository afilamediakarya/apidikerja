<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class masterAktivitas extends Model
{
    use HasFactory;
    protected $table = 'tb_master_aktivitas';

    public function kelompok_jabatan() {
        return $this->hasOne('App\Models\kelompok_jabatan','id','id_kelompok_jabatan');
    }

}
