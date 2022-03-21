<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class situasi extends Model
{
    use HasFactory;
    protected $table = 'tb_situasi';
    protected $with = ['indikator'];

    public function situasi(){
        return $this->belongsTo('App\Models\perilaku','id','id_perilaku');
    }
    
    public function indikator(){
        return $this->hasMany('App\Models\indikator','id_situasi','id');
    }
}
