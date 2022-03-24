<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class satuan_kerja extends Model
{
    use HasFactory;
    protected $table ='tb_satuan_kerja';

    public function pegawai(){
        return $this->belongsTo('App\Models\pegawai','id_satuan_kerja','id');
    }

    public function informasi(){
        return $this->belongsTo('App\Models\informasi','id_satuan_kerja','id');
    }

    public function jabatan(){
        return $this->belongsTo('App\Models\jabatan','id_satuan_kerja','id');
    }

     public function kegiatan(){
        return $this->belongsTo('App\Models\kegiatan','id_satuan_kerja','id');
    }

    public function skp(){
        return $this->belongsTo('App\Models\skp','id_skp','id');
    }

    
}
