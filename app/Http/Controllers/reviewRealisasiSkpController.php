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

                if (!is_null($value->id_pegawai)) {
                   array_push($groupId,$value->id_pegawai);       
                }  
             }

             foreach ($groupId as $x => $vv) {

                $res = DB::table('tb_pegawai')->select('tb_pegawai.nama', 'tb_pegawai.nip', 'tb_pegawai.jenis_jabatan', 'tb_skp.id AS id_skp', 'tb_pegawai.id AS id_pegawai')->join('tb_skp','tb_pegawai.id', '=', 'tb_skp.id_pegawai')->where('id_pegawai',$vv)->get();

                if (count($res) > 0) {
                    array_push($groupSkpPegawai,$res);
                } 

              }      
        }

          foreach ($groupSkpPegawai as $bnb => $llo) {
            $getDataStatus = [];
              foreach ($llo as $vv => $bb) {
                 $getReview = review_realisasi_skp::where('id_skp',$bb->id_skp)->get()->pluck('kesesuaian')->toArray();
                 // $getDataStatus[] = $getReview; 
                }

                // return $getDataStatus;
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
                    'jenis_jabatan'=>$llo[0]->jenis_jabatan,
                    'id_pegawai'=>$llo[0]->id_pegawai,
                    'status'=>$status,
                ];

          }

        // foreach ($getData as $key => $value) {
        //     $res = DB::table('tb_pegawai')->select('tb_pegawai.nama', 'tb_pegawai.nip', 'tb_pegawai.jenis_jabatan', 'tb_skp.id AS id_skp', 'tb_pegawai.id AS id_pegawai')->join('tb_skp','tb_pegawai.id', '=', 'tb_skp.id_pegawai')->where('id_pegawai',$value->id_pegawai)->first();
        //     if (isset($res)) {
                
        //         $getReview = review_realisasi_skp::where('id_skp',$res->id_skp)->get()->pluck('kesesuaian')->toArray();
        //         if (in_array("tidak", $getReview) == true && in_array("ya", $getReview) == true){
        //             $status = 'Belum Sesuai';
        //         }
        //         else if(in_array("ya", $getReview) == true && in_array("tidak", $getReview) == false){
        //             $status = 'Selesai';
        //         }else{
        //             $status = 'Belum Review';
        //         }

        //         $myArray[$key] = [
        //             'nama'=>$res->nama,
        //             'nip'=>$res->nip,
        //             'jenis_jabatan'=>$res->jenis_jabatan,
        //             'id_skp'=>$res->id_skp,
        //             'id_pegawai'=>$res->id_pegawai,
        //             'status'=>$status,
        //         ];
                
        //     }
        // }

        if ($myArray) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $myArray
            ]);
        }else{
            return response()->json([
                'message' => 'Failed',
                'status' => false
            ]);
        }
    }

    public function skpbyId($params,$bulan){
        
              $result = [];
             $groupSkpAtasan = [];

            $get_skp_atasan = DB::table('tb_skp')->select('id_skp_atasan')->where('id_pegawai',$params)->groupBy('tb_skp.id_skp_atasan')->get();

        // return $get_skp_atasan;

        foreach ($get_skp_atasan as $key => $value) {
            $getSkpByAtasan = DB::table('tb_skp')->select('id','rencana_kerja','jenis')->where('id',$value->id_skp_atasan)->first();
            $skpChild = skp::with('aspek_skp')->where('id_skp_atasan',$getSkpByAtasan->id)->where('id_pegawai',$params)->get();
            foreach ($skpChild as $xx => $vv) {
                $realisasi_bulan = DB::table('tb_review_realisasi_skp')->where('id_skp',$vv->id)->where('bulan',$bulan)->first();   
                $result[$key]['atasan'] = $getSkpByAtasan;
                $result[$key]['skp_child'] = $skpChild;
                $result[$key]['skp_child'][$xx]['realisasi_bulan'] = $realisasi_bulan;
            }
            
            // $result[$key]['atasan'] = $getSkpByAtasan;
            // $result[$key]['skp_child'] = $skpChild;
      
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
