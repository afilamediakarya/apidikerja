<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\bidang;
use Validator;
class bidangController extends Controller
{
    public function list(){
        $data = bidang::latest()->get();

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
            'id_kepala_bidang' => 'required|numeric',
            'nama_bidang' => 'required|string',
            'nama_jabatan_bidang' => 'required|string',
            'tahun' => 'required|string',
            'status_kepala_bidang' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        $data = new bidang();
        $data->id_kepala_bidang	 = $request->id_kepala_bidang;
        $data->kode_bidang = '001';
        $data->nama_bidang = $request->nama_bidang;
        $data->nama_jabatan_bidang = $request->nama_jabatan_bidang;
        $data->tahun = $request->tahun;
        $data->status_kepala_bidang = $request->status_kepala_bidang;
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
        $data = bidang::where('id',$params)->first();

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
            'id_kepala_bidang' => 'required|numeric',
            'nama_bidang' => 'required|string',
            'nama_jabatan_bidang' => 'required|string',
            'tahun' => 'required|string',
            'status_kepala_bidang' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        $data = bidang::where('id',$params)->first();
        $data->id_kepala_bidang	 = $request->id_kepala_bidang;
        $data->kode_bidang = '';
        $data->nama_bidang = $request->nama_bidang;
        $data->nama_jabatan_bidang = $request->nama_jabatan_bidang;
        $data->tahun = $request->tahun;
        $data->status_kepala_bidang = $request->status_kepala_bidang;
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
        $data = bidang::where('id',$params)->first();
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
