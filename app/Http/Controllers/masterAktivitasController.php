<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use Auth;
use DB;
use App\Models\masterAktivitas;

class masterAktivitasController extends Controller
{
    public function list(){
        $data = masterAktivitas::with("kelompok_jabatan")->get();

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

    public function option(){
        $jabatan = DB::table('tb_jabatan')->select('id_kelompok_jabatan')->where('id_pegawai',Auth::user()->id_pegawai)->first();
        $data = masterAktivitas::select('id','aktivitas as value')->where('id_kelompok_jabatan',$jabatan->id_kelompok_jabatan)->get();
        return $data;
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'kelompok_jabatan' => 'required',
            'aktivitas' => 'required',
            'satuan' => 'required',
            'waktu' => 'required',
            'jenis' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $data = new masterAktivitas();
        $request->jenis == 'umum' ? $data->id_kelompok_jabatan = 0 : $data->id_kelompok_jabatan = $request->kelompok_jabatan;
        $data->aktivitas = $request->aktivitas;
        $data->satuan = $request->satuan;
        $data->waktu = $request->waktu;
        $data->jenis = $request->jenis;
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

        // return DB::table('tb_master_aktivitas')->join('tb_kelompok_jabatan','')->where('id',$params)->first();
        // $data = DB::table('tb_master_aktivitas')->select('tb_master_aktivitas.id','tb_master_aktivitas.aktivitas','tb_master_aktivitas.satuan','tb_master_aktivitas.waktu','tb_master_aktivitas.jenis','tb_kelompok_jabatan.id as kelompok_jabatan')->join('tb_kelompok_jabatan','tb_master_aktivitas.id_kelompok_jabatan','=','tb_kelompok_jabatan.id')->where('tb_master_aktivitas.id',$params)->first();

        $data = masterAktivitas::with("kelompok_jabatan")->where('id',$params)->first();
    
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
            'kelompok_jabatan' => 'required',
            'aktivitas' => 'required',
            'satuan' => 'required',
            'waktu' => 'required',
            'jenis' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $data = masterAktivitas::where('id',$params)->first();
        $request->jenis == 'umum' ? $data->id_kelompok_jabatan = 0 : $data->id_kelompok_jabatan = $request->kelompok_jabatan;      
        $data->aktivitas = $request->aktivitas;
        $data->satuan = $request->satuan;
        $data->waktu = $request->waktu;
        $data->jenis = $request->jenis;
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

    public function delete($params)
    {
        $data = masterAktivitas::where('id', $params)->first();
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
}
