<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class perilaku extends Model
{
    use HasFactory;
    protected $table = 'tb_perilaku';
    protected $with = ['situasi'];
    
    public function situasi(){
        return $this->hasMany('App\Models\situasi','id_perilaku','id');
    }
}
