<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class aspek_skp extends Model
{
    use HasFactory;
    protected $table = 'tb_aspek_skp';
    protected $with =['target_skp','realisasi_skp'];

    public function skp(){
        return $this->belongsTo('App\Models\skp','id_skp','id');
    }

    public function target_skp(){
        return $this->hasMany('App\Models\target_skp','id_aspek_skp','id');
    }

    public function realisasi_skp(){
        return $this->hasMany('App\Models\realisasi_skp','id_aspek_skp','id');
    }

    // public function user() {
    //     return $this->hasOne('App\Models\realisasi_skp', 'id', 'id_aspek_skp');
    // }

}
