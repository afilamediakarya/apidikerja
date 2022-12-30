<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Models\kelompok_jabatan;
use Validator;
use Auth;
class KelompokjabatanController extends Controller
{
    public function list(){

        $data = DB::table('tb_kelompok_jabatan')->select('tb_kelompok_jabatan.id','tb_kelompok_jabatan.kelompok','tb_kelompok_jabatan.id_jenis_jabatan',
        'tb_jenis_jabatan.jenis_jabatan')->join('tb_jenis_jabatan','tb_kelompok_jabatan.id_jenis_jabatan','=','tb_jenis_jabatan.id')->get();

        if ($data) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $data
            ]);
        } else {
            return response()->json([
                'message' => 'Failed',
                'status' => false,
                'data' => $data
            ]);
        }
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'kelompok' => 'required',
            'jenis_jabatan' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $data = new kelompok_jabatan();
        $data->kelompok = $request->kelompok;
        $data->id_jenis_jabatan = $request->jenis_jabatan;
        $data->user_insert = Auth::user()->id;
        $data->save();

        if ($data) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $data
            ]);
        } else {
            return response()->json([
                'message' => 'Failed',
                'status' => false
            ]);
        }
    }

    public function show($params)
    {  
       
        $data = DB::table('tb_kelompok_jabatan')->select('tb_kelompok_jabatan.id','tb_kelompok_jabatan.kelompok','tb_kelompok_jabatan.id_jenis_jabatan as jenis_jabatan')->join('tb_jenis_jabatan','tb_kelompok_jabatan.id_jenis_jabatan','=','tb_jenis_jabatan.id')->where('tb_kelompok_jabatan.id',$params)->first();
    
        if ($data) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $data
            ]);
        } else {
            return response()->json([
                'message' => 'Failed',
                'status' => false
            ]);
        }
    }

    public function update($params, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kelompok' => 'required',
            'jenis_jabatan' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $data = kelompok_jabatan::where('id',$params)->first();
        $data->kelompok = $request->kelompok;
        $data->id_jenis_jabatan = $request->jenis_jabatan;
        $data->user_update = Auth::user()->id;
        $data->save();

        if ($data) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $data
            ]);
        } else {
            return response()->json([
                'message' => 'Failed',
                'status' => false
            ]);
        }
    }

    public function delete($params)
    {
        $data = kelompok_jabatan::where('id', $params)->first();
        $data->delete();

        if ($data) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $data,
            ]);
        } else {
            return response()->json([
                'message' => 'Failed',
                'status' => false
            ]);
        }
    }

    public function getOption($params){
        $data = array();
        if ($params !== 'general') {
             $data = kelompok_jabatan::select('id','kelompok as value')->where('id_jenis_jabatan',$params)->orderBy('id', 'DESC')->get();

        }else{
             $data = kelompok_jabatan::select('id','kelompok as value')->orderBy('id', 'DESC')->get();

        }
               return response()->json($data);
    }
}
