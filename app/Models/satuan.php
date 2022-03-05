<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class satuan extends Model
{
    use HasFactory;
    protected $table = 'tb_satuan';

    public function pegawai(){
        return $this->belongsTo('App\Models\produk','id_satuan_kerja','id');
    }
}
