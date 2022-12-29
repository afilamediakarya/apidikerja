<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class indikator extends Model
{
    use HasFactory;
    protected $table = 'tb_indikator'; 

    public function situasi(){
        return $this->belongsTo('App\Models\perilaku','id','id_situasi');
    }
}
