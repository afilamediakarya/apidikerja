<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\jadwal;
use DB;
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
            'tahapan' => 'required|string',
            'sub_tahapan' => 'required|string',
            'tanggal_awal' => 'required|date',
            'tanggal_akhir' => 'required|date',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        $data = new jadwal();
        $data->tahapan = $request->tahapan;
        $data->sub_tahapan = $request->sub_tahapan;
        $data->tanggal_awal = $request->tanggal_awal;
        $data->tanggal_akhir = $request->tanggal_akhir;
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
            'tahapan' => 'required|string',
            'sub_tahapan' => 'required|string',
            'tanggal_awal' => 'required|date',
            'tanggal_akhir' => 'required|date',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        $data =  jadwal::where('id',$params)->first();
        $data->tahapan = $request->tahapan;
        $data->sub_tahapan = $request->sub_tahapan;
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

    public function optionTahapan(){
        $data = DB::table('tb_tahapan')->get();

        foreach ($data as $key => $value) {
            $result[$key] = [
                'id' => $value->id,
                'value'=> $value->tahapan
            ];
        }

        return response()->json($result);
    }

    public function optionSubTahapan($params){
        $data = DB::table('tb_sub_tahapan')->where('id_tahapan',$params)->get();
        foreach ($data as $key => $value) {
            $result[$key] = [
                'id' => $value->id,
                'value'=> $value->sub_tahapan
            ];
        }

        return response()->json($result);
    }
}
