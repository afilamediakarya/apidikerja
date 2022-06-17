<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\realisasi_skp;
use App\Models\review_realisasi_skp;
use App\Models\atasan;
use App\Models\skp;
use Auth;
use Validator;
use DB;
class realisasiController extends Controller
{
    public function list($params){
        // return $params;
        if ($params == 'kepala') {
            return $this->list_realisasi_skp_kepala();
        }else{
            return $this->list_realisasi_skp_pegawai();
        }
    }

    public function list_realisasi_skp_kepala(){
        $result = [];
        $status_review = '';
        $skp = skp::with('aspek_skp','reviewRealisasiSkp')->where('id_pegawai',Auth::user()->id_pegawai)->get();
        foreach ($skp as $key => $value) {
            $getReview = $value['reviewRealisasiSkp']->pluck('kesesuaian')->toArray();
                
               if (in_array("tidak", $getReview) == true && in_array("ya", $getReview) == true){
                    $status_review = 'Belum Sesuai';
                }
                else if(in_array("ya", $getReview) == true && in_array("tidak", $getReview) == false){
                    $status_review = 'Selesai';
                }else{
                    $status_review = 'Belum Review';
                }

             $skp[$key]['status_review'] = $status_review;    
        }

        if ($skp) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $skp
            ]);
        }else{
            return response()->json([
                'message' => 'empty data',
                'status' => false,
                 'data' => $skp
            ]);
        }
    }

    public function list_realisasi_skp_pegawai(){
        //  $result = [];
        // $groupSkpAtasan = [];
        // $skpUtama = '';
        // $skpTambahan = '';
        // $jabatanByPegawai = DB::table('tb_jabatan')->where('id_pegawai',Auth::user()->id_pegawai)->first();
        // $get_skp_atasan = DB::table('tb_skp')->select('id_skp_atasan')->where('id_pegawai',Auth::user()->id_pegawai)->groupBy('tb_skp.id_skp_atasan')->get();

        // foreach ($get_skp_atasan as $key => $value) {
        //     $getRencanaKerjaAtasan = '';
        //    if (!is_null($jabatanByPegawai->parent_id)) {
        //        $getSkpAtasan = DB::table('tb_skp')->select('id','rencana_kerja','jenis')->where('id',$value->id_skp_atasan)->first();
        //         $getRencanaKerjaAtasan = [
        //         'id' => $getSkpAtasan->id,
        //         'rencana_kerja' =>$getSkpAtasan->rencana_kerja
        //      ];
        //    }else{
        //      $getKegiatan= DB::table('tb_kegiatan')->select('id','nama_kegiatan','kode_kegiatan')->where('id',$value->id_skp_atasan)->first();

        //      if (isset($getKegiatan)) {
        //         $getRencanaKerjaAtasan = [
        //             'id' => $getKegiatan->id,
        //             'rencana_kerja' =>$getKegiatan->nama_kegiatan
        //          ];
        //      }else{
        //          $getRencanaKerjaAtasan = [];
        //      }

             
        //    }
           
        //     if ($getRencanaKerjaAtasan != []) {

        //         $skpUtama = skp::with('aspek_skp','reviewRealisasiSkp')->where('id_skp_atasan',$getRencanaKerjaAtasan['id'])->where('jenis','utama')->where('id_pegawai',Auth::user()->id_pegawai)->get();

        //             foreach ($skpUtama as $keys => $values) {
        //                 $getReview = $values['reviewRealisasiSkp']->pluck('kesesuaian')->toArray();
                            
        //                    if (in_array("tidak", $getReview) == true && in_array("ya", $getReview) == true){
        //                         $status_review = 'Belum Sesuai';
        //                     }
        //                     else if(in_array("ya", $getReview) == true && in_array("tidak", $getReview) == false){
        //                         $status_review = 'Selesai';
        //                     }else{
        //                         $status_review = 'Belum Review';
        //                     }

        //                  $skpUtama[$keys]['status_review'] = $status_review;    
        //             }

        //             $skpTambahan = skp::with('aspek_skp','reviewRealisasiSkp')->where('id_skp_atasan',$getRencanaKerjaAtasan['id'])->where('jenis','tambahan')->where('id_pegawai',Auth::user()->id_pegawai)->get();

        //                foreach ($skpTambahan as $keys => $values) {
        //                 $getReview = $values['reviewRealisasiSkp']->pluck('kesesuaian')->toArray();
                            
        //                    if (in_array("tidak", $getReview) == true && in_array("ya", $getReview) == true){
        //                         $status_review = 'Belum Sesuai';
        //                     }
        //                     else if(in_array("ya", $getReview) == true && in_array("tidak", $getReview) == false){
        //                         $status_review = 'Selesai';
        //                     }else{
        //                         $status_review = 'Belum Review';
        //                     }

        //                  $skpTambahan[$keys]['status_review'] = $status_review;    
        //             }

        //     }else{
        //         $skpUtama = [];
        //         $skpTambahan = [];
        //     }
        //     $result[$key]['atasan'] = $getRencanaKerjaAtasan;
        //     $result[$key]['skp_utama'] = $skpUtama;
        //     $result[$key]['skp_tambahan'] = $skpTambahan;
        // }      

        $result = [];
        $groupSkpAtasan = [];
        $skpChild = '';
        $jabatanByPegawai = DB::table('tb_jabatan')->where('id_pegawai',Auth::user()->id_pegawai)->first();
        $get_skp_atasan = DB::table('tb_skp')->select('id_skp_atasan')->where('id_pegawai',Auth::user()->id_pegawai)->groupBy('tb_skp.id_skp_atasan')->where('jenis','utama')->get();

        foreach ($get_skp_atasan as $key => $value) {
            $getRencanaKerjaAtasan = [];
           if (!is_null($jabatanByPegawai->parent_id)) {
              if (!is_null($value->id_skp_atasan)) {
                   $getSkpAtasan = DB::table('tb_skp')->select('id','rencana_kerja','jenis')->where('id',$value->id_skp_atasan)->where('jenis','utama')->first();
                $getRencanaKerjaAtasan = [
                    'id' => $getSkpAtasan->id,
                    'rencana_kerja' =>$getSkpAtasan->rencana_kerja
                 ];
              }
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

           $tes[] = $getRencanaKerjaAtasan;
           
            if ($getRencanaKerjaAtasan != []) {
                $skpUtama = skp::with('aspek_skp')->where('id_skp_atasan',$getRencanaKerjaAtasan['id'])->where('jenis','utama')->where('id_pegawai',Auth::user()->id_pegawai)->get();

                    foreach ($skpUtama as $keys => $values) {
                        $getReview = $values['reviewRealisasiSkp']->pluck('kesesuaian')->toArray();
                            
                           if (in_array("tidak", $getReview) == true && in_array("ya", $getReview) == true){
                                $status_review = 'Belum Sesuai';
                            }
                            else if(in_array("ya", $getReview) == true && in_array("tidak", $getReview) == false){
                                $status_review = 'Selesai';
                            }else{
                                $status_review = 'Belum Review';
                            }

                         $skpUtama[$keys]['status_review'] = $status_review;    
                    }

            }
            $result['utama'][$key]['atasan'] = $getRencanaKerjaAtasan;
            $result['utama'][$key]['skp'] = $skpUtama;
        }      

        $skp_tambahan = skp::with('aspek_skp')->where('jenis','tambahan')->where('id_pegawai',Auth::user()->id_pegawai)->get();

        foreach ($skp_tambahan as $keys => $values) {
            $getReview = $values['reviewRealisasiSkp']->pluck('kesesuaian')->toArray();
                
               if (in_array("tidak", $getReview) == true && in_array("ya", $getReview) == true){
                    $status_review = 'Belum Sesuai';
                }
                else if(in_array("ya", $getReview) == true && in_array("tidak", $getReview) == false){
                    $status_review = 'Selesai';
                }else{
                    $status_review = 'Belum Review';
                }

             $skp_tambahan[$keys]['status_review'] = $status_review;    
        }

        $result['tambahan'] = $skp_tambahan;

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

    public function store(Request $request){
        // return $request->all();
        $validator = Validator::make($request->all(),[
            'id_aspek_skp' => 'required|array',
            'realisasi_bulanan' => 'required|array',
            'bulan' => 'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        $data = '';
        $tes= [];
        if ($request->bulan != 0) {
            for ($i=0; $i < count($request->id_aspek_skp); $i++) { 
                $data = realisasi_skp::where('id_aspek_skp',$request->id_aspek_skp[$i])->where('bulan',$request->bulan)->first();
                $data->realisasi_bulanan = $request->realisasi_bulanan[$i];
                $data->save();
            }
        } else {
            for ($i=0; $i < count($request->id_aspek_skp); $i++) { 
           
                for ($y=0; $y < count($request->realisasi_bulanan[$i]) ; $y++) { 
                  $data = realisasi_skp::where('id_aspek_skp',$request->id_aspek_skp[$i])->where('bulan',$y+1)->first();
                  $data->realisasi_bulanan = $request->realisasi_bulanan[$i][$y];
                  $data->save();
                }
            }
        }

        // return $tes;       

        if ($data) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $data
            ]);
        }else{
            return response()->json([
                'message' => 'Failed',
                'status' => false
            ]);
        }
    }

    public function show($params){
        $data = realisasi_skp::where('id',$params)->first();

        if ($data) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $data
            ]);
        }else{
            return response()->json([
                'message' => 'Failed',
                'status' => false
            ]);
        }
    }

    public function update($params,Request $request){
        $validator = Validator::make($request->all(),[
            'id_aspek_skp' => 'required|numeric',
            'realisasi_bulanan' => 'required|numeric',
            'bulan' => 'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        $data = realisasi_skp::where('id',$params)->first();
        $data->id_aspek_skp = $request->id_aspek_skp;
        $data->realisasi_bulanan = $request->realisasi_bulanan;
        $data->bulan = $request->bulan;
        $data->save();

        if ($data) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $data
            ]);
        }else{
            return response()->json([
                'message' => 'Failed',
                'status' => false
            ]);
        }
    }

    public function delete($params){
        $data = realisasi_skp::where('id',$params)->first();
        $data->delete();

        if ($data) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
            ]);
        }else{
            return response()->json([
                'message' => 'Failed',
                'status' => false
            ]);
        }
    }

    public function realisasiKuantitas($params,$id_skp){
        $num = 0;
        $aktivitas = DB::table('tb_aktivitas')->whereMonth('tanggal','=',$params)->where('id_pegawai',Auth::user()->id_pegawai)->where('id_skp',$id_skp)->get();
        // return $aktivitas;

        if (count($aktivitas) > 0) {
            foreach ($aktivitas as $key => $value) {
                $num += $value->hasil;
            }
        }

        return response()->json([
            'message' => 'Success',
            'status' => true,
            'data' => $num
        ]);
    }
}
