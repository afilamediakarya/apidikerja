<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class informasi extends Model
{
    use HasFactory;
    protected $table = 'tb_informasi';
    protected $with = ['satuan_kerja'];
    public function satuan_kerja(){
        return $this->hasMany('App\Models\satuan_kerja','id','id_satuan_kerja');
    }
}
