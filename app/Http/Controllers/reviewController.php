<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\review_skp;
use Validator;
class reviewController extends Controller
{
     public function list(){
        $data = review_skp::latest()->get();
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
            'id_skp' => 'required|numeric',
            'keterangan' => 'required',
            'kesesuaian' => 'required',
            'bulan' => 'required',
            'tahun' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        $data = new review_skp();
        $data->id_skp = $request->id_skp;
        $data->keterangan = $request->keterangan;
        $data->kesesuaian = $request->kesesuaian;
        $data->bulan = $request->bulan;
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
        $data = review_skp::where('id',$params)->first();

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
            'id_skp' => 'required|numeric',
            'keterangan' => 'required',
            'kesesuaian' => 'required',
            'bulan' => 'required',
            'tahun' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        $data = review_skp::where('id',$params)->first();
        $data->id_skp = $request->id_skp;
        $data->keterangan = $request->keterangan;
        $data->kesesuaian = $request->kesesuaian;
        $data->bulan = $request->bulan;
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
        $data = review_skp::where('id',$params)->first();
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
