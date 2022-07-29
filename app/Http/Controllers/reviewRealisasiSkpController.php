<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\review_realisasi_skp;
use App\Models\skp;
use App\Models\atasan;
use Auth;
use DB;
use Validator;
class reviewRealisasiSkpController extends Controller
{

    public function list(){
        // $getData = atasan::where('id_penilai',Auth::user()->id_pegawai)->get();
        $jabatanPegawai = DB::table('tb_jabatan')->select('id')->where('id_pegawai',Auth::user()->id_pegawai)->first();
        $myArray = [];
        $groupId = [];
         $groupSkpPegawai = [];
        if (isset($jabatanPegawai)) {
            $getData = DB::table('tb_jabatan')->where('parent_id',$jabatanPegawai->id)->get(); 
        
            $status = '';
            foreach ($getData as $key => $value) {

                if (!is_null($value->id)) {
                   array_push($groupId,$value->id);       
                }  
             }

             foreach ($groupId as $x => $vv) {
                $res = DB::table('tb_pegawai')->select('tb_pegawai.id','tb_pegawai.nama', 'tb_pegawai.nip', 'tb_pegawai.jenis_jabatan', 'tb_skp.id AS id_skp', 'tb_pegawai.id AS id_pegawai','tb_review.kesesuaian AS kesesuaian')->join('tb_jabatan','tb_pegawai.id','=','tb_jabatan.id_pegawai')->join('tb_skp','tb_jabatan.id', '=', 'tb_skp.id_jabatan')->join('tb_review','tb_skp.id','=','tb_review.id_skp')->where('tb_jabatan.id',$vv)->get();

                if (count($res) > 0) {
                    array_push($groupSkpPegawai,$res);
                } 

              }      
        }

        // return $groupSkpPegawai;

          foreach ($groupSkpPegawai as $bnb => $llo) {
            $getDataStatus = [];
              foreach ($llo as $vv => $bb) {
                 $getReview = review_realisasi_skp::where('id_skp',$bb->id_skp)->get()->pluck('kesesuaian')->toArray();
                }
                  if (in_array("tidak", $getReview) == true && in_array("ya", $getReview) == true){
                    $status = 'Belum Sesuai';
                }
                else if(in_array("ya", $getReview) == true && in_array("tidak", $getReview) == false){
                    $status = 'Selesai';
                }else{
                    $status = 'Belum Review';
                }

                $myArray[$bnb] = [
                    'nama'=>$llo[0]->nama,
                    'nip'=>$llo[0]->nip,
                    'jenis_jabatan'=>$llo[0]->nama_jabatan,
                    'id_pegawai'=>$llo[0]->id_pegawai,
                    'status'=>$status,
                ];

          }

        if ($myArray) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $myArray
            ]);
        }else{
            return response()->json([
                'message' => 'Failed',
                'status' => false,
                'data' => $myArray
            ]);
        }
    }

    public function skpbyId($params,$bulan){
        
              $result = [];
             $groupSkpAtasan = [];
             $tes = [];
            $get_skp_atasan = DB::table('tb_skp')->select('id_skp_atasan')->where('id_pegawai',$params)->groupBy('tb_skp.id_skp_atasan')->get();

        // return $get_skp_atasan;

        if (!is_null($get_skp_atasan)) {
               foreach ($get_skp_atasan as $key => $value) {
                    $getSkpByAtasan = DB::table('tb_skp')->select('id','rencana_kerja','jenis')->where('id',$value->id_skp_atasan)->first();
                    if (!is_null($getSkpByAtasan)) {
                        $skpChild = skp::with('aspek_skp')->where('id_skp_atasan',$getSkpByAtasan->id)->where('id_pegawai',$params)->get();
                        foreach ($skpChild as $xx => $vv) {
                            $realisasi_bulan = DB::table('tb_review_realisasi_skp')->where('id_skp',$vv->id)->where('bulan',$bulan)->first();   
                            $result['utama'][$key]['atasan'] = $getSkpByAtasan;
                            $result['utama'][$key]['skp_child'] = $skpChild;
                            $result['utama'][$key]['skp_child'][$xx]['realisasi_bulan'] = $realisasi_bulan;
                        }
                    }

              
                }  
        }    

        $skp_tambahan = skp::with('aspek_skp')->where('jenis','tambahan')->where('id_pegawai',$params)->get();

        if (count($skp_tambahan) > 0) {
            foreach ($skp_tambahan as $yy => $vals) {
                $realisasi_bulan = DB::table('tb_review_realisasi_skp')->where('id_skp',$vals->id)->where('bulan',$bulan)->first();   
                $result['tambahan'] = $skp_tambahan;
                $result['tambahan'][$yy]['realisasi_bulan'] = $realisasi_bulan;
            }
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

    public function store(Request $request){
        // return $request->all();
        $validator = Validator::make($request->all(),[
            'id_skp' => 'required|array',
            'keterangan' => 'required|array',
            'kesesuaian' => 'required|array',
            'bulan' => 'required|array',
        ]);



        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        for ($i=0; $i < count($request->id_skp); $i++) { 
            $data = review_realisasi_skp::where('id_skp',$request->id_skp[$i])->where('bulan',$request->bulan[$i])->first();
            $data->keterangan = $request->keterangan[$i];
            $data->kesesuaian = $request->kesesuaian[$i];
            $data->save();
        }

        // return $cek;


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
}
