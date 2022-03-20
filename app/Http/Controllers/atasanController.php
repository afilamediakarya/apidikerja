<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\atasan;
use Validator;
use Auth;
class atasanController extends Controller
{
    public function list(){
        $data = atasan::latest()->get();

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
            'id_penilai' => 'required|numeric',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        $data = new atasan();
        $data->id_penilai = $request->id_penilai;
        $data->id_pegawai = Auth::user()->id_pegawai;
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
        $data = atasan::where('id',$params)->first();

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
            'id_penilai' => 'required|numeric'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        $data = atasan::where('id',$params)->first();
        $data->id_penilai = $request->id_penilai;
        $data->id_pegawai = Auth::user()->id_pegawai;
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
        $data = atasan::where('id',$params)->first();
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
