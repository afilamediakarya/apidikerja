<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class review_realisasi_skp extends Model
{
    use HasFactory;
    protected $table = 'tb_review_realisasi_skp';

        public function skp(){
        return $this->belongsTo('App\Models\skp','id_skp','id');
    }
    
    
}
