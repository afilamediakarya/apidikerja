<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\kelas_jabatan;
use DB;
class kelasJabatanController extends Controller
{
    public function list(){
        $data = kelas_jabatan::latest()->get();

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
            'kelas_jabatan' => 'required|numeric',
            'besaran_tpp' => 'required|numeric'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        $data = new kelas_jabatan();
        $data->kelas_jabatan = $request->kelas_jabatan;
        $data->besaran_tpp = $request->besaran_tpp;
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
        $data = kelas_jabatan::where('id',$params)->first();

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
            'kelas_jabatan' => 'required|numeric',
            'besaran_tpp' => 'required|numeric'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        $data = kelas_jabatan::where('id',$params)->first();
        $data->kelas_jabatan = $request->kelas_jabatan;
        $data->besaran_tpp = $request->besaran_tpp;
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
        $data = kelas_jabatan::where('id',$params)->first();
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
    
    public function optionKelasJabatan(){
        $result = [];
        $data = DB::table('tb_kelas_jabatan')->latest()->get();
        foreach ($data as $key => $value) {
            $result[$key] = [
                'id' => $value->id,
                'value'=> $value->kelas_jabatan
            ];
        }
        return response()->json($result);
    }
}
