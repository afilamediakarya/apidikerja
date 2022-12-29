<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class target_skp extends Model
{
    use HasFactory;
    protected $table = 'tb_target_skp';
    // protected $with = ['aspek_skp'];
    public function aspek_skp(){
        return $this->belongsTo('App\Models\aspek_skp','id','id_aspek_skp');
    }
}
