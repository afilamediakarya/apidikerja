<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\realisasi_skp;
use App\Models\atasan;
use App\Models\skp;
use Auth;
use Validator;
use DB;
class realisasiController extends Controller
{
    public function list(){
        // $result = [];
        // // $atasan = atasan::where('id_pegawai',Auth::user()->id_pegawai)->first();
        // $jabatanPegawai = DB::table('tb_jabatan')->select('id')->where('id_pegawai',Auth::user()->id_pegawai)->first();
        
        // if (isset($jabatanPegawai)) {
        //     $getData = DB::table('tb_jabatan')->where('parent_id',$jabatanPegawai->id)->get(); 

        //     $get_skp_atasan = skp::where('id_pegawai',$jabatanPegawai->id)->get(); 
        //     foreach($get_skp_atasan as $key => $value){
            
        //         $getsubSKp = skp::with('aspek_skp')->where('id_skp_atasan',$value->id)->get();
        //         $result[$key] = [
        //             'id_pegawai'=>$value['id_pegawai'],
        //             'nama_atasan'=>$value['pegawai'][0]['nama'],
        //             'rencana_kerja'=>$value['rencana_kerja'],
        //             'sub_skp'=> $getsubSKp
        //         ];
        //     }
        // }
        // // return $jabatanPegawai;
        
       

        // if ($result) {
        //     return response()->json([
        //         'message' => 'Success',
        //         'status' => true,
        //         'data' => $result
        //     ]);
        // }else{
        //     return response()->json([
        //         'message' => 'Failed',
        //         'status' => false,
        //         'data' => $result
        //     ]);
        // }

        $result = [];
        $groupSkpAtasan = [];

        $jabatanByPegawai = DB::table('tb_jabatan')->where('id_pegawai',Auth::user()->id_pegawai)->first();
        $get_skp_atasan = DB::table('tb_skp')->select('id_skp_atasan')->where('id_pegawai',Auth::user()->id_pegawai)->groupBy('tb_skp.id_skp_atasan')->get();

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

             $getRencanaKerjaAtasan = [
                'id' => $getKegiatan->id,
                'rencana_kerja' =>$getKegiatan->nama_kegiatan
             ];
           }
           
            $skpChild = skp::with('aspek_skp')->where('id_skp_atasan',$getRencanaKerjaAtasan['id'])->where('id_pegawai',Auth::user()->id_pegawai)->get();
            $result[$key]['atasan'] = $getRencanaKerjaAtasan;
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

    public function store(Request $request){
        // dd($request->all());
        $validator = Validator::make($request->all(),[
            'id_aspek_skp' => 'required|array',
            'realisasi_bulanan' => 'required|array',
            'bulan' => 'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        for ($i=0; $i < count($request->id_aspek_skp); $i++) { 
            $data = realisasi_skp::where('id_aspek_skp',$request->id_aspek_skp[$i])->where('bulan',$request->bulan)->first();
            $data->realisasi_bulanan = $request->realisasi_bulanan[$i];
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
}
