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
        $myArray = [];
        $data = array();
        $jabatanPegawai = DB::table('tb_jabatan')->select('id')->where('id_pegawai',Auth::user()->id_pegawai)->first();
        if (isset($jabatanPegawai)) {
            $myArray = DB::table('tb_jabatan')->select('tb_jabatan.id','tb_jabatan.id_pegawai','tb_jabatan.nama_jabatan','tb_pegawai.nama','tb_pegawai.nip')->join('tb_pegawai','tb_jabatan.id_pegawai','=','tb_pegawai.id')->where('parent_id',$jabatanPegawai->id)->get();  

            // return $myArray;

            foreach ($myArray as $key => $value) {
                $skp = DB::table('tb_skp')->select('tb_review.kesesuaian')->join('tb_review','tb_review.id_skp','=','tb_skp.id')->where('tb_skp.id_jabatan',$value->id)->where('tb_skp.tahun',request('tahun'))->get()->toArray();
                $filter_ = array_column($skp, 'kesesuaian');
                if (in_array("tidak", $filter_) == true && in_array("ya", $filter_) == true){
                    $status = 'Belum Sesuai';
                }
                else if(in_array("ya", $filter_) == true && in_array("tidak", $filter_) == false){
                    $status = 'Selesai';
                }else{
                    $status = 'Belum Review';
                }
                $value->status = $status;
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
        $type = request('type');
        $tahun = request('tahun');

        $jabatanByPegawai =  DB::table('tb_jabatan')->select('tb_jabatan.id','tb_jabatan.id_pegawai')->join('tb_pegawai','tb_jabatan.id_pegawai','=','tb_pegawai.id')->where('tb_jabatan.id_pegawai',$params)->first();

        $result = skp::select('tb_skp.id','tb_skp.id_jabatan','tb_skp.id_skp_atasan','tb_skp.jenis','tb_skp.rencana_kerja','tb_skp.tahun','tb_review.keterangan','tb_review.kesesuaian')->with('aspek_skp')->join('tb_review','tb_review.id_skp','=','tb_skp.id')->where('tahun',$tahun)->where('id_jabatan',$jabatanByPegawai->id)->orderBy('jenis','ASC')->get();
            foreach ($result as $key => $value) {
             if (!is_null($value->id_skp_atasan)) {
                 $value->skp_atasan = DB::table('tb_skp')->where('id',$value->id_skp_atasan)->first()->rencana_kerja;
             }else{
                 $value->skp_atasan = '-';
             }

                if ($value->jenis == 'utama') {
                    $value->jenis_kinerja = 'A. Kinerja Utama';
                } else {
                    $value->jenis_kinerja = 'B. Kinerja Tambahan';
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

}
