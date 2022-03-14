<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class aspek_skp extends Model
{
    use HasFactory;
    protected $table = 'tb_aspek_skp';
    protected $with =['skp'];

    public function skp(){
        return $this->belongsTo('App\Models\skp','id_skp','id');
    }

    public function target_skp(){
        return $this->hasOne('App\Models\target_skp','id','id_aspek_skp');
    }

    public function user() {
        return $this->hasOne('App\Models\realisasi_skp', 'id', 'id_aspek_skp');
    }

}
