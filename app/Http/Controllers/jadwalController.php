<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\jadwal;
use Validator;
class jadwalController extends Controller
{
    public function list(){
        $data = jadwal::latest()->get();

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
            'nama_kegiatan' => 'required|string',
            'nama_sub_kegiatan' => 'required|string',
            'tanggal_awal' => 'required|date',
            'tanggal_akhir' => 'required|date',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        $data = new jadwal();
        $data->nama_kegiatan = $request->nama_kegiatan;
        $data->nama_sub_kegiatan = $request->nama_sub_kegiatan;
        $data->tanggal_awal = $request->tanggal_awal;
        $data->tanggal_akhir = $request->tanggal_akhir;
        $data->tahun = date('Y');
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
        $data = jadwal::where('id',$params)->first();

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
            'nama_kegiatan' => 'required|string',
            'nama_sub_kegiatan' => 'required|string',
            'tanggal_awal' => 'required|date',
            'tanggal_akhir' => 'required|date',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        $data =  jadwal::where('id',$params)->first();
        $data->nama_kegiatan = $request->nama_kegiatan;
        $data->nama_sub_kegiatan = $request->nama_sub_kegiatan;
        $data->tanggal_awal = $request->tanggal_awal;
        $data->tanggal_akhir = $request->tanggal_akhir;
        $data->tahun = date('Y');
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
        $data = jadwal::where('id',$params)->first();
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
