<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VerfikasiController extends Controller
{
    public function pendidikanFormal()
    {
        $satuanKerja = DB::table('tb_pegawai')
            ->select("id_satuan_kerja")
            ->where("id", Auth::user()->id_pegawai)
            ->first();

        $listPegawai = DB::table('tb_pegawai')
            ->select('id', 'nama')
            ->where('id_satuan_kerja', $satuanKerja->id_satuan_kerja)
            ->get();

        foreach ($listPegawai as $key => $value) {
            // $pendidikan = 
        }
        return $listPegawai;
    }
}
