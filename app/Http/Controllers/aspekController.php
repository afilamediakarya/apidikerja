<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\aspek_skp;
use Validator;
class aspekController extends Controller
{
    public function list(){
        $data = aspek_skp::latest()->get();

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
            'iki' => 'required|string',
            'aspek_skp' => 'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        $data = new aspek_skp();
        $data->id_skp = $request->id_skp;
        $data->iki = $request->iki;
        $data->aspek_skp = $request->aspek_skp;
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
        $data = aspek_skp::where('id',$params)->first();

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
            'iki' => 'required|string',
            'aspek_skp' => 'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        $data = aspek_skp::where('id',$params)->first();
        $data->id_skp = $request->id_skp;
        $data->iki = $request->iki;
        $data->aspek_skp = $request->aspek_skp;
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
        $data = aspek_skp::where('id',$params)->first();
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
