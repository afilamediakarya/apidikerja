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
        $skp = [];
        $atasan = '';
        $jabatanByPegawai = DB::table('tb_jabatan')->where('id_pegawai',Auth::user()->id_pegawai)->first();
        
        $jabatan_atasan = DB::table('tb_jabatan')->where('id',$jabatanByPegawai->parent_id)->first();

        if (isset($jabatan_atasan->id_pegawai)) {
            $atasan = DB::table('tb_jabatan')->select('tb_pegawai.nama','tb_pegawai.nip','tb_pegawai.golongan','tb_jabatan.nama_jabatan','tb_satuan_kerja.nama_satuan_kerja')->join('tb_pegawai','tb_pegawai.id', '=', 'tb_jabatan.id_pegawai')->join('tb_satuan_kerja','tb_satuan_kerja.id', '=', 'tb_pegawai.id_satuan_kerja')->where('tb_pegawai.id',$jabatan_atasan->id_pegawai)->first();
        }

        $current = DB::table('tb_jabatan')->select('tb_pegawai.nama','tb_pegawai.nip','tb_pegawai.golongan','tb_jabatan.nama_jabatan','tb_satuan_kerja.nama_satuan_kerja')->join('tb_pegawai','tb_pegawai.id', '=', 'tb_jabatan.id_pegawai')->join('tb_satuan_kerja','tb_satuan_kerja.id', '=', 'tb_pegawai.id_satuan_kerja')->where('tb_pegawai.id',Auth::user()->id_pegawai)->first();

        // $skp = skp::with('aspek_skp')->where('id_pegawai',Auth::user()->id_pegawai)->get();
        $skpUtama = skp::with('aspek_skp')->where('jenis','utama')->where('id_pegawai',Auth::user()->id_pegawai)->get();
        $skpTambahan = skp::with('aspek_skp')->where('jenis','tambahan')->where('id_pegawai',Auth::user()->id_pegawai)->get(); 


        $result['atasan'] = $atasan;
        $result['pegawai_dinilai'] = $current;
        $result['skp']['utama'] = $skpUtama;
        $result['skp']['tambahan'] = $skpTambahan;
        
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
   
        if (isset($jabatanByPegawai)) {
            $jabatan_atasan = DB::table('tb_jabatan')->where('id',$jabatanByPegawai->parent_id)->first();
        }
        

        if (isset($jabatan_atasan->id_pegawai)) {
            $atasan = DB::table('tb_jabatan')->select('tb_pegawai.nama','tb_pegawai.nip','tb_pegawai.golongan','tb_jabatan.nama_jabatan','tb_satuan_kerja.nama_satuan_kerja')->join('tb_pegawai','tb_pegawai.id', '=', 'tb_jabatan.id_pegawai')->join('tb_satuan_kerja','tb_satuan_kerja.id', '=', 'tb_pegawai.id_satuan_kerja')->where('tb_pegawai.id',$jabatan_atasan->id_pegawai)->first();
        }

        $current = DB::table('tb_jabatan')->select('tb_pegawai.nama','tb_pegawai.nip','tb_pegawai.golongan','tb_jabatan.nama_jabatan','tb_satuan_kerja.nama_satuan_kerja')->join('tb_pegawai','tb_pegawai.id', '=', 'tb_jabatan.id_pegawai')->join('tb_satuan_kerja','tb_satuan_kerja.id', '=', 'tb_pegawai.id_satuan_kerja')->where('tb_pegawai.id',Auth::user()->id_pegawai)->first();       
        // return $jabatanByPegawai;
        $get_skp_atasan = DB::table('tb_skp')->select('id_skp_atasan')->where('id_jabatan',$jabatan_atasan->id)->groupBy('tb_skp.id_skp_atasan')->get();

        $result['atasan'] = $atasan;
        $result['pegawai_dinilai'] = $current;

        // return $get_skp_atasan;

        foreach ($get_skp_atasan as $key => $value) {
            $getRencanaKerjaAtasan = [];
           if (!is_null($jabatanByPegawai->parent_id)) {
              
              if (!is_null($value->id_skp_atasan)) {
               $getSkpAtasan = DB::table('tb_skp')->select('id','rencana_kerja','jenis')->where('id',$value->id_skp_atasan)->first();
               if (isset($getSkpAtasan)) {
                   $getRencanaKerjaAtasan = [
                    'id' => $getSkpAtasan->id,
                    'rencana_kerja' =>$getSkpAtasan->rencana_kerja
                 ];
               }
              }
                
           }else{
             // $getKegiatan= DB::table('tb_kegiatan')->select('id','nama_kegiatan','kode_kegiatan')->where('id',$value->id_skp_atasan)->first();

             // if (isset($getKegiatan)) {
             //    $getRencanaKerjaAtasan = [
             //        'id' => $getKegiatan->id,
             //        'rencana_kerja' =>$getKegiatan->nama_kegiatan
             //     ];
             // }

             
           }
           
            if ($getRencanaKerjaAtasan != []) {
                $skpChild = skp::with('aspek_skp')->where('id_skp_atasan',$getRencanaKerjaAtasan['id'])->where('id_jabatan',$jabatanByPegawai->id)->where('jenis','utama')->get();
            }else{
                $skpChild = [];
            }
            
            if (count($getRencanaKerjaAtasan) > 0 && count($skpChild) > 0) {
                $result['skp']['utama'][$key]['atasan'] = $getRencanaKerjaAtasan;
                $result['skp']['utama'][$key]['skp_child'] = $skpChild;
            }
        
        }      

          $skp_tambahan = skp::with('aspek_skp')->where('jenis','tambahan')->where('id_jabatan',$jabatanByPegawai->id)->get();

        if (count($skp_tambahan) > 0) {
           $result['skp']['tambahan'] = $skp_tambahan;
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
