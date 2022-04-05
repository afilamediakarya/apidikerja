<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class aktivitas extends Model
{
    use HasFactory;
    protected $table = 'tb_aktivitas';
    protected $with = ['skp'];

    public function skp() {
        return $this->hasMany('App\Models\skp', 'id', 'id_skp');
    }


}
