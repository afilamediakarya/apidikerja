<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\realisasi_skp;
use Validator;
class realisasiController extends Controller
{
    public function list(){
        $data = realisasi_skp::latest()->get();

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
            'id_aspek_skp' => 'required|numeric',
            'target_bulanan' => 'required|numeric',
            'bulan' => 'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        $data = new realisasi_skp();
        $data->id_aspek_skp = $request->id_aspek_skp;
        $data->target_bulanan = $request->target_bulanan;
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
            'target_bulanan' => 'required|numeric',
            'bulan' => 'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        $data = realisasi_skp::where('id',$params)->first();
        $data->id_aspek_skp = $request->id_aspek_skp;
        $data->target_bulanan = $request->target_bulanan;
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
