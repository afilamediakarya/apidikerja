<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\skp;
use App\Models\aspek_skp;
use App\Models\target_skp;
use App\Models\atasan;
use App\Models\review_realisasi_skp;
use App\Models\review_skp;
use App\Models\realisasi_skp;
use App\Models\satuan;
use App\Models\jabatan;
use App\Models\kegiatan;
use App\Models\pegawai;
use DB;
use Validator;
use Auth;

class laporanController extends Controller
{
    public function laporanSkp($params){
          if ($params == 'kepala') {
            return $this->laporanSkpKepala();
        }else{
            return $this->laporanSkpPegawai();
        }
    }   

    public function laporanSkpKepala(){
        $result = [];
        $atasan = '';
        $jabatanByPegawai = DB::table('tb_jabatan')->where('id_pegawai',Auth::user()->id_pegawai)->first();
        
        $jabatan_atasan = DB::table('tb_jabatan')->where('id',$jabatanByPegawai->parent_id)->first();

        if (isset($jabatan_atasan->id_pegawai)) {
            $atasan = DB::table('tb_jabatan')->select('tb_pegawai.nama','tb_pegawai.nip','tb_pegawai.golongan','tb_jabatan.nama_jabatan','tb_satuan_kerja.nama_satuan_kerja')->join('tb_pegawai','tb_pegawai.id', '=', 'tb_jabatan.id_pegawai')->join('tb_satuan_kerja','tb_satuan_kerja.id', '=', 'tb_pegawai.id_satuan_kerja')->where('tb_pegawai.id',$jabatan_atasan->id_pegawai)->first();
        }

        $current = DB::table('tb_jabatan')->select('tb_pegawai.nama','tb_pegawai.nip','tb_pegawai.golongan','tb_jabatan.nama_jabatan','tb_satuan_kerja.nama_satuan_kerja')->join('tb_pegawai','tb_pegawai.id', '=', 'tb_jabatan.id_pegawai')->join('tb_satuan_kerja','tb_satuan_kerja.id', '=', 'tb_pegawai.id_satuan_kerja')->where('tb_pegawai.id',Auth::user()->id_pegawai)->first();
        $skp = skp::with('aspek_skp')->where('id_pegawai',Auth::user()->id_pegawai)->get();

        $result['atasan'] = $atasan;
        $result['pegawai_dinilai'] = $current;
        $result['skp'] = $skp;
        
        if ($result) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $result
            ]);
        }else{
            return response()->json([
                'message' => 'empty data',
                'status' => false,
                 'data' => $result
            ]);
        }
    }   

    public function laporanSkpPegawai(){
         $result = [];
        $groupSkpAtasan = [];
        $skpChild = '';
        $atasan = '';
        $jabatanByPegawai = DB::table('tb_jabatan')->where('id_pegawai',Auth::user()->id_pegawai)->first();
        
        $jabatan_atasan = DB::table('tb_jabatan')->where('id',$jabatanByPegawai->parent_id)->first();

        if (isset($jabatan_atasan->id_pegawai)) {
            $atasan = DB::table('tb_jabatan')->select('tb_pegawai.nama','tb_pegawai.nip','tb_pegawai.golongan','tb_jabatan.nama_jabatan','tb_satuan_kerja.nama_satuan_kerja')->join('tb_pegawai','tb_pegawai.id', '=', 'tb_jabatan.id_pegawai')->join('tb_satuan_kerja','tb_satuan_kerja.id', '=', 'tb_pegawai.id_satuan_kerja')->where('tb_pegawai.id',$jabatan_atasan->id_pegawai)->first();
        }

        $current = DB::table('tb_jabatan')->select('tb_pegawai.nama','tb_pegawai.nip','tb_pegawai.golongan','tb_jabatan.nama_jabatan','tb_satuan_kerja.nama_satuan_kerja')->join('tb_pegawai','tb_pegawai.id', '=', 'tb_jabatan.id_pegawai')->join('tb_satuan_kerja','tb_satuan_kerja.id', '=', 'tb_pegawai.id_satuan_kerja')->where('tb_pegawai.id',Auth::user()->id_pegawai)->first();       
        // return $jabatanByPegawai;
        $get_skp_atasan = DB::table('tb_skp')->select('id_skp_atasan')->where('id_pegawai',Auth::user()->id_pegawai)->groupBy('tb_skp.id_skp_atasan')->get();

        $result['atasan'] = $atasan;
        $result['pegawai_dinilai'] = $current;

        foreach ($get_skp_atasan as $key => $value) {
            $getRencanaKerjaAtasan = '';
           if (!is_null($jabatanByPegawai->parent_id)) {
               $getSkpAtasan = DB::table('tb_skp')->select('id','rencana_kerja','jenis')->where('id',$value->id_skp_atasan)->first();
                $getRencanaKerjaAtasan = [
                'id' => $getSkpAtasan->id,
                'rencana_kerja' =>$getSkpAtasan->rencana_kerja
             ];
           }else{
             $getKegiatan= DB::table('tb_kegiatan')->select('id','nama_kegiatan','kode_kegiatan')->where('id',$value->id_skp_atasan)->first();

             if (isset($getKegiatan)) {
                $getRencanaKerjaAtasan = [
                    'id' => $getKegiatan->id,
                    'rencana_kerja' =>$getKegiatan->nama_kegiatan
                 ];
             }else{
                 $getRencanaKerjaAtasan = [];
             }

             
           }
           
            if ($getRencanaKerjaAtasan != []) {
                $skpChild = skp::with('aspek_skp')->where('id_skp_atasan',$getRencanaKerjaAtasan['id'])->where('id_pegawai',Auth::user()->id_pegawai)->get();
            }else{
                $skpChild = [];
            }
            $result['skp'][$key]['atasan'] = $getRencanaKerjaAtasan;
            $result['skp'][$key]['skp_child'] = $skpChild;
      
        }      

        if ($result) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $result
            ]);
        }else{
            return response()->json([
                'message' => 'empty data',
                'status' => false,
                 'data' => $result
            ]);
        }
    }

}
