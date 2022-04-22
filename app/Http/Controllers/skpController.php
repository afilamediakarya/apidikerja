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
class skpController extends Controller
{
    public function list(){
        $result = [];
        $groupSkpAtasan = [];

        $get_skp_atasan = DB::table('tb_skp')->select('id_skp_atasan')->where('id_pegawai',Auth::user()->id_pegawai)->groupBy('tb_skp.id_skp_atasan')->get();

        // return $get_skp_atasan;

        foreach ($get_skp_atasan as $key => $value) {
            $getSkpByAtasan = DB::table('tb_skp')->select('id','rencana_kerja','jenis')->where('id',$value->id_skp_atasan)->first();
            $skpChild = skp::with('aspek_skp')->where('id_skp_atasan',$getSkpByAtasan->id)->where('id_pegawai',Auth::user()->id_pegawai)->get();
            $result[$key]['atasan'] = $getSkpByAtasan;
            $result[$key]['skp_child'] = $skpChild;
      
        }      

        // foreach ($groupSkpAtasan as $k => $v) {
        //     $skpChild = skp::where('id_skp_atasan',$v->id)->where('id_pegawai',Auth::user()->id_pegawai)->get();
        //     // $result[$key] = $v;
        //     $result[$key]['sub_skp'] = $skpChild;
        // }

        // return $result;
        // $skp_pegawai = [];
        // // $atasan = atasan::where('id_pegawai',Auth::user()->id_pegawai)->first();
        // $atasan = DB::table('tb_jabatan')->where('id_pegawai',Auth::user()->id_pegawai)->first();
        // $getDataAtasan = jabatan::where('id',$atasan->parent_id)->first();
        // // return $atasan;/
        
        // if (isset($atasan)) {
        //     $getSkp = skp::where('id_pegawai',Auth::user()->id_pegawai)->get();
        //     // foreach ($getSkp as $k => $v) {
        //     //     if ($v->jenis == 'utama') {
        //     //         $skp_pegawai['utama'][] = $v;
        //     //     }else{
        //     //         $skp_pegawai['tambahan'][] = $v;
        //     //     }
        //     // }

        //     // return $getSkp;
        //     $get_skp_atasan = skp::with('pegawai')->where('id_pegawai',$getDataAtasan->id_pegawai)->get();
        //     return $skp_pegawai;
        //     foreach($get_skp_atasan as $key => $value){
        //         $getsubSKp = skp::with('aspek_skp')->where('id_skp_atasan',$value->id)->get();
        //         $result[$key] = [
        //             'id_pegawai'=>$value['id_pegawai'],
        //             'nama_atasan'=>$value['pegawai']['nama'],
        //             'rencana_kerja'=>$value['rencana_kerja'],
        //             'sub_skp'=> $getsubSKp
        //         ];
        //     }
        // }

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
        // return $request;
        $validator = Validator::make($request->all(),[
            'id_satuan_kerja' => 'required|numeric',
            'id_skp_atasan' => 'required|numeric',
            'jenis' => 'required',
            'rencana_kerja' => 'required',
            'tahun' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(),422);       
        }
        
        $skp = new skp();
        $skp->id_pegawai = Auth::user()->id_pegawai;
        $skp->id_satuan_kerja = $request->id_satuan_kerja;
        $skp->id_skp_atasan = $request->id_skp_atasan;
        $skp->jenis = $request->jenis;
        $skp->rencana_kerja = $request->rencana_kerja;
        $skp->tahun = $request->tahun;
        $skp->save();

        $review = new review_skp();
        $review->id_skp = $skp->id;
        $review->kesesuaian = 'tidak';
        $review->save();

        for ($i=0; $i < 13; $i++) { 
            $review_realisasi_skp = new review_realisasi_skp();
            $review_realisasi_skp->id_skp = $skp->id;
            $review_realisasi_skp->kesesuaian = 'tidak';
            $review_realisasi_skp->bulan = $i+1;
            $review_realisasi_skp->save();
        }

        foreach ($request['aspek'] as $key => $value) {
            $aspek = new aspek_skp();
            $aspek->id_skp = $skp->id;
            $aspek->aspek_skp = $value['type_aspek'];
            $aspek->iki = $value['iki'];
            $aspek->satuan = $value['satuan'];
            $aspek->save();

            for ($x=0; $x < 12; $x++) { 
                $realisasi_skp = new realisasi_skp();
                $realisasi_skp->id_aspek_skp = $aspek->id;
                $realisasi_skp->realisasi_bulanan = 0;
                $realisasi_skp->bulan = $x+1;
                $realisasi_skp->save();
            }

            foreach ($value['target'] as $index => $res) {
                $target = new target_skp();
                $target->id_aspek_skp = $aspek->id;
                $target->target = $res;
                $target->bulan = $index+1;
                $target->save();
            }
        }


        if ($skp) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $skp
            ]);
        }else{
            return response()->json([
                'message' => 'Failed',
                'status' => false
            ],422);
        }
    }

    public function show($params){
        $data = skp::with('aspek_skp')->where('id',$params)->first();

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
            'id_satuan_kerja' => 'required|numeric',
            'id_skp_atasan' => 'required|numeric',
            'jenis' => 'required',
            'rencana_kerja' => 'required',
            'tahun' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        $skp = skp::where('id',$params)->first();
        $skp->id_pegawai = Auth::user()->id_pegawai;
        $skp->id_satuan_kerja = $request->id_satuan_kerja;
        $skp->id_skp_atasan = $request->id_skp_atasan;
        $skp->jenis = $request->jenis;
        $skp->rencana_kerja = $request->rencana_kerja;
        $skp->tahun = $request->tahun;
        $skp->save();

        if($skp){
            $delete_skp = aspek_skp::where('id',$skp->id)->delete();
            // $delete_skp->delete
        }

        foreach ($request['aspek'] as $key => $value) {
            $aspek = new aspek_skp();
            $aspek->id_skp = $skp->id;
            $aspek->aspek_skp = $value['type_aspek'];
            $aspek->iki = $value['iki'];
            $aspek->satuan = $value['satuan'];
            $aspek->save();
            foreach ($value['target'] as $index => $res) {
                $target = new target_skp();
                $target->id_aspek_skp = $aspek->id;
                $target->target = $res;
                $target->bulan = $index+1;
                $target->save();
            }
        }

        if ($skp) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $skp
            ]);
        }else{
            return response()->json([
                'message' => 'Failed',
                'status' => false
            ],422);
        }
    }

    public function delete($params){
        $data = skp::where('id',$params)->first();
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

    public function satuan(){
        $result = [];
        $data = satuan::where('status','active')->latest()->get();
        foreach ($data as $key => $value) {
            $result[$key] = [
                'value'=> $value->nama_satuan
            ];
        }

        return response()->json($result);
        // return collect($data)->pluck('nama_satuan')->toArray();
    }

    public function optionSkp(){
        $result = [];
        $atasan = DB::table('tb_jabatan')->where('id_pegawai',Auth::user()->id_pegawai)->first();
        // $checkDataAtasan = DB::table('tb_jabatan')->where('id',$atasan->parent_id)->first();
        $pegawai = pegawai::where('id',Auth::user()->id_pegawai)->first();
        // return $checkDataAtasan;

        if (isset($atasan)) {
            // $getJabatan = jabatan::where('id',$atasan['id_penilai'])->first();
            $getJabatan = DB::table('tb_jabatan')->where('id',$atasan->parent_id)->first();
            // return $getJabatan;
            if (isset($getJabatan)) {
                if ($getJabatan->level != "1") {
                    $getSkp = skp::where('id_pegawai',$getJabatan->id_pegawai)->get();
                    foreach ($getSkp as $key => $value) {
                        $result[$key] = [
                            'id' => $value->id,
                            'value'=> $value->rencana_kerja
                        ];
                    }
                    return $result;
                    // return collect($getSkp)->pluck('rencana_kerja','id')->toArray();
                }else{
                    $kegiatan = kegiatan::where('id_satuan_kerja',$pegawai['id_satuan_kerja'])->latest()->get();
                    foreach ($kegiatan as $key => $value) {
                        $result[$key] = [
                            'id' => $value->id,
                            'value'=> $value->nama_kegiatan
                        ];
                    }
                    return $result;
                    // return collect($kegiatan)->pluck('nama_kegiatan','id')->toArray();
                }
            }else{
                return response()->json([
                    'message' => 'Data tidak ada',
                    'status' => false
                ]);
            }
        }else{
            return response()->json([
                'message' => 'Data tidak ada',
                'status' => false
            ]); 
        }
        
    }

}
