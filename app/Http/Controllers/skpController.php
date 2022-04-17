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
use Validator;
use Auth;
class skpController extends Controller
{
    public function list(){
        $result = [];
        $atasan = atasan::where('id_pegawai',Auth::user()->id_pegawai)->first();
        
        if (isset($atasan)) {
            $get_skp_atasan = skp::where('id_pegawai',$atasan->id_penilai)->get();
            foreach($get_skp_atasan as $key => $value){
                
                $getsubSKp = skp::with('aspek_skp')->where('id_skp_atasan',$value->id)->get();
                $result[$key] = [
                    'id_pegawai'=>$value['id_pegawai'],
                    'nama_atasan'=>$value['pegawai'][0]['nama'],
                    'rencana_kerja'=>$value['rencana_kerja'],
                    'sub_skp'=> $getsubSKp
                ];
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
        // return $request;
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
            ]);
        }
    }

    public function show($params){
        $data = skp::where('id',$params)->first();

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
            ]);
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
        $atasan  = atasan::where('id_pegawai',Auth::user()->id_pegawai)->first();
        $pegawai = pegawai::where('id',Auth::user()->id_pegawai)->first();
    
        if (isset($atasan)) {
            $getJabatan = jabatan::where('id',$atasan['id_penilai'])->first();

            if (isset($getJabatan)) {
                if ($getJabatan['level'] != "1") {
                    $getSkp = skp::where('id_pegawai',$getJabatan['id_pegawai'])->get();
                    return collect($getSkp)->pluck('rencana_kerja','id')->toArray();
                }else{
                    $kegiatan = kegiatan::where('id_satuan_kerja',$pegawai['id_satuan_kerja'])->latest()->get();
                    return collect($kegiatan)->pluck('nama_kegiatan','id')->toArray();
                }
            }else{
                return response()->json([
                    'message' => 'Data tidak ada',
                    'status' => false
                ],422);
            }
        }else{
            return response()->json([
                'message' => 'Data tidak ada',
                'status' => false
            ],422); 
        }
        
    }

}
