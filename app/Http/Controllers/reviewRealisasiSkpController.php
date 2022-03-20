<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\review_realisasi_skp;
use App\Models\atasan;
use Auth;
use DB;
use Validator;
class reviewRealisasiSkpController extends Controller
{

    public function list(){
        $getData = atasan::where('id_penilai',Auth::user()->id_pegawai)->get();
        $myArray = [];
        $status = '';
        foreach ($getData as $key => $value) {
            $res = DB::table('tb_pegawai')->select('tb_pegawai.nama', 'tb_pegawai.nip', 'tb_pegawai.jenis_jabatan', 'tb_skp.id AS id_skp', 'tb_pegawai.id AS id_pegawai')->join('tb_skp','tb_pegawai.id', '=', 'tb_skp.id_pegawai')->where('id_pegawai',$value->id_pegawai)->first();
            if (isset($res)) {
                
                $getReview = review_realisasi_skp::where('id_skp',$res->id_skp)->get()->pluck('kesesuaian')->toArray();
                if (in_array("tidak", $getReview) == true && in_array("ya", $getReview) == true){
                    $status = 'Belum Sesuai';
                }
                else if(in_array("ya", $getReview) == true && in_array("tidak", $getReview) == false){
                    $status = 'Selesai';
                }else{
                    $status = 'Belum Review';
                }

                $myArray[$key] = [
                    'nama'=>$res->nama,
                    'nip'=>$res->nip,
                    'jenis_jabatan'=>$res->jenis_jabatan,
                    'id_skp'=>$res->id_skp,
                    'id_pegawai'=>$res->id_pegawai,
                    'status'=>$status,
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
                'message' => 'Failed',
                'status' => false
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
