<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class riwayatPendidikan extends Model
{
    use HasFactory;

    protected $table = 'tb_riwayat_pendidikan';

    public function pegawai()
    {
        return $this->hasMany('App\Models\pegawai', 'id', 'id_pegawai');
    }
}
