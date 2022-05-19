<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\review_skp;
use App\Models\review_realisasi_skp;
use App\Models\skp;
use App\Models\atasan;
use App\Models\pegawai;
use App\Models\jabatan;
use Auth;
use DB;
use Validator;
class reviewController extends Controller
{

    public function list(){
        // $getData = atasan::where('id_penilai',Auth::user()->id_pegawai)->get();
       // return Auth::user()->id_pegawai;
        $myArray = [];
        $groupId = [];
        $groupSkpPegawai = [];
        $jabatanPegawai = DB::table('tb_jabatan')->select('id')->where('id_pegawai',Auth::user()->id_pegawai)->first();
        // return $jabatanPegawai;

        if (isset($jabatanPegawai)) {
            $getData = DB::table('tb_jabatan')->where('parent_id',$jabatanPegawai->id)->get(); 
            // return $getData;
            $status = '';
            foreach ($getData as $key => $value) {

                if (!is_null($value->id_pegawai)) {
                   array_push($groupId,$value->id_pegawai);       
                }

            }

            foreach ($groupId as $x => $vv) {
                $res = DB::table('tb_pegawai')->select('tb_pegawai.nama', 'tb_pegawai.nip', 'tb_pegawai.jenis_jabatan', 'tb_pegawai.id AS id_pegawai_','tb_review.kesesuaian AS kesesuaian','tb_skp.id AS id_skp','tb_jabatan.nama_jabatan')->join('tb_skp','tb_pegawai.id', '=', 'tb_skp.id_pegawai')->join('tb_review','tb_skp.id','=','tb_review.id_skp')->join('tb_jabatan','tb_pegawai.id','=','tb_jabatan.id_pegawai')->where('tb_pegawai.id',$vv)->get(); 

                if (count($res) > 0) {
                    array_push($groupSkpPegawai,$res);
                }       
            }  

            // return $groupSkpPegawai;


            foreach ($groupSkpPegawai as $bnb => $llo) {
                $getDataStatus = [];
                foreach ($llo as $vv => $bb) {
                    foreach ($llo as $cc => $klp) {
                        $getDataStatus[] = $klp->kesesuaian;  
                    }
                     
                }

                if (in_array("tidak", $getDataStatus) == true && in_array("ya", $getDataStatus) == true){
                    $status = 'Belum Sesuai';
                }
                else if(in_array("ya", $getDataStatus) == true && in_array("tidak", $getDataStatus) == false){
                    $status = 'Selesai';
                }else{
                    $status = 'Belum Review';
                }

                 $myArray[$bnb] = [
                    'nama'=>$llo[0]->nama,
                    'nip'=>$llo[0]->nip,
                    'jabatan'=>$llo[0]->nama_jabatan,
                    'id_pegawai'=>$llo[0]->id_pegawai_,
                    'status' => $status
                ];

            }  

        }

         if ($myArray) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $myArray
            ]);
        }else{
            return response()->json([
                'message' => 'Data belum ada',
                'status' => false,
                'data' => $myArray
            ]);
        }


       
      
    }

    public function group_list(){
        $myArray = [];
        $groupId = [];
        $groupSkpReviewPegawai = [];
        $groupSkpRealisasiPegawai = [];
        $status_review = '';
        $status_realisasi = '';
        $jabatanPegawai = DB::table('tb_jabatan')->select('id')->where('id_pegawai',Auth::user()->id_pegawai)->first();

        if (isset($jabatanPegawai)) {
            $getData = DB::table('tb_jabatan')->where('parent_id',$jabatanPegawai->id)->get(); 
            $status = '';
            foreach ($getData as $key => $value) {

                if (!is_null($value->id_pegawai)) {
                   array_push($groupId,$value->id_pegawai);       
                }
              
            }

            foreach ($groupId as $x => $vv) {
                $reviewSkp = DB::table('tb_pegawai')->select('tb_pegawai.nama', 'tb_pegawai.nip', 'tb_pegawai.jenis_jabatan', 'tb_pegawai.id AS id_pegawai_','tb_review.kesesuaian AS kesesuaian','tb_skp.id AS id_skp','tb_jabatan.nama_jabatan')->join('tb_skp','tb_pegawai.id', '=', 'tb_skp.id_pegawai')->join('tb_review','tb_skp.id','=','tb_review.id_skp')->join('tb_jabatan','tb_pegawai.id','=','tb_jabatan.id_pegawai')->where('tb_pegawai.id',$vv)->get(); 

                $reviewRealisasiSkp = DB::table('tb_pegawai')->select('tb_pegawai.nama', 'tb_pegawai.nip', 'tb_pegawai.jenis_jabatan', 'tb_skp.id AS id_skp', 'tb_pegawai.id AS id_pegawai')->join('tb_skp','tb_pegawai.id', '=', 'tb_skp.id_pegawai')->where('id_pegawai',$vv)->get();

                if (count($reviewSkp) > 0) {
                    array_push($groupSkpReviewPegawai,$reviewSkp);
                } 
                
                if (count($groupSkpRealisasiPegawai) > 0) {
                    array_push($groupSkpRealisasiPegawai,$reviewSkp);
                } 
            }  

            foreach ($groupSkpReviewPegawai as $bnb => $llo) {
                $getDataStatusReview = [];
                $getDataStatusRealisasi = [];
                // Review
                foreach ($llo as $vv => $bb) {
                    foreach ($llo as $cc => $klp) {
                        $getDataStatusReview[] = $klp->kesesuaian;  
                    }
                     
                }

                if (in_array("tidak", $getDataStatusReview) == true && in_array("ya", $getDataStatusReview) == true){
                    $status_review = 'Belum Sesuai';
                }
                else if(in_array("ya", $getDataStatusReview) == true && in_array("tidak", $getDataStatusReview) == false){
                    $status_review = 'Selesai';
                }else{
                    $status_review = 'Belum Review';
                }
                // 

                // REALISASI
                foreach ($llo as $hg => $bbl) {
                    $getReview = review_realisasi_skp::where('id_skp',$bbl->id_skp)->get()->pluck('kesesuaian')->toArray();
                }

                if (in_array("tidak", $getReview) == true && in_array("ya", $getReview) == true){
                    $status_realisasi = 'Belum Sesuai';
                }
                else if(in_array("ya", $getReview) == true && in_array("tidak", $getReview) == false){
                    $status_realisasi = 'Selesai';
                }else{
                    $status_realisasi = 'Belum Review';
                }

                // 

               

                 $myArray[$bnb] = [
                    'nama'=>$llo[0]->nama,
                    'nip'=>$llo[0]->nip,
                    'jabatan'=>$llo[0]->nama_jabatan,
                    'id_pegawai'=>$llo[0]->id_pegawai_,
                    'status_review' => $status_review,
                    'status_realisasi' => $status_realisasi
                ];

            }  

            // foreach ($groupSkpRealisasiPegawai as $njk => $ll) {
            //     $getDataStatus = [];
            //       foreach ($ll as $hg => $bbl) {
            //          $getReview = review_realisasi_skp::where('id_skp',$bbl->id_skp)->get()->pluck('kesesuaian')->toArray();
            //          // $getDataStatus[] = $getReview; 
            //         }
    
            //         // return $getDataStatus;
            //           if (in_array("tidak", $getReview) == true && in_array("ya", $getReview) == true){
            //             $status_realisasi = 'Belum Sesuai';
            //         }
            //         else if(in_array("ya", $getReview) == true && in_array("tidak", $getReview) == false){
            //             $status_realisasi = 'Selesai';
            //         }else{
            //             $status_realisasi = 'Belum Review';
            //         }
    
            //         $myArray[$njk] = [
            //             'nama'=>$ll[0]->nama,
            //             'nip'=>$ll[0]->nip,
            //             'jenis_jabatan'=>$ll[0]->jenis_jabatan,
            //             'id_pegawai'=>$ll[0]->id_pegawai,
            //             'status'=>$status_realisasi,
            //         ];
    
            //   }

        }

         if ($myArray) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $myArray
            ]);
        }else{
            return response()->json([
                'message' => 'Data belum ada',
                'status' => false,
                'data' => $myArray
            ]);
        }
    }

    public function store(Request $request){
        // return $request->all();
        $validator = Validator::make($request->all(),[
            'id_skp' => 'required|array',
            'keterangan' => 'required|array',
            'kesesuaian' => 'required|array',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        for ($i=0; $i < count($request->id_skp); $i++) { 
            $data = review_skp::where('id_skp',$request->id_skp[$i])->first();
            $data->keterangan = $request['keterangan'][$i];
            $data->kesesuaian = $request['kesesuaian'][$i];
            $data->save();
        }


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

    public function skpbyId($params){
          $result = [];
        $groupSkpAtasan = [];

        $get_skp_atasan = DB::table('tb_skp')->select('id_skp_atasan')->where('id_pegawai',$params)->groupBy('tb_skp.id_skp_atasan')->get();

        // return $get_skp_atasan;

        foreach ($get_skp_atasan as $key => $value) {
            $getSkpByAtasan = DB::table('tb_skp')->select('id','rencana_kerja','jenis')->where('id',$value->id_skp_atasan)->first();
            $skpChild = skp::with('aspek_skp','review_skp')->where('id_skp_atasan',$getSkpByAtasan->id)->where('id_pegawai',$params)->get();
            $result[$key]['atasan'] = $getSkpByAtasan;
            $result[$key]['skp_child'] = $skpChild;
      
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
