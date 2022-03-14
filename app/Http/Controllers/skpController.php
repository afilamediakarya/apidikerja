<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\skp;
use Validator;
class skpController extends Controller
{
    public function list(){
        $data = skp::latest()->get();

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

    public function store(Request $request){
        $validator = Validator::make($request->all(),[
            'id_pegawai' => 'required|numeric',
            'id_satuan_kerja' => 'required|numeric',
            'id_skp_atasan' => 'required|numeric',
            'jenis' => 'required',
            'rencana_kerja' => 'required',
            'tahun' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        $data = new skp();
        $data->id_pegawai = $request->id_pegawai;
        $data->id_satuan_kerja = $request->id_satuan_kerja;
        $data->id_skp_atasan = $request->id_skp_atasan;
        $data->jenis = $request->jenis;
        $data->rencana_kerja = $request->rencana_kerja;
        $data->tahun = $request->tahun;
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
            'id_pegawai' => 'required|numeric',
            'id_satuan_kerja' => 'required|numeric',
            'id_skp_atasan' => 'required|numeric',
            'jenis' => 'required',
            'rencana_kerja' => 'required',
            'tahun' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        $data = skp::where('id',$params)->first();
        $data->id_pegawai = $request->id_pegawai;
        $data->id_satuan_kerja = $request->id_satuan_kerja;
        $data->id_skp_atasan = $request->id_skp_atasan;
        $data->jenis = $request->jenis;
        $data->rencana_kerja = $request->rencana_kerja;
        $data->tahun = $request->tahun;
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
}
