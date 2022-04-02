<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\absen;
use Validator;
use Carbon\Carbon;
class absenController extends Controller
{
    public function list(){
        $data = absen::latest()->get();

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
        // return $request;
        $validator = Validator::make($request->all(),[
            'id_pegawai' => 'required|numeric',
            'tanggal_absen' => 'required|date',
            'waktu_absen' => 'required',
            'status' => 'required',
            'jenis' => 'required',
            'location_auth' => 'required',
            'face_auth' => 'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        $data = new absen();
        $data->id_pegawai = $request->id_pegawai;
        $data->tanggal_absen = $request->tanggal_absen;
        $data->waktu_absen = $request->waktu_absen;
        $data->status = $request->status;
        $data->jenis = $request->jenis;
        $data->location_auth = $request->location_auth;
        $data->face_auth = $request->face_auth;
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
        $data = absen::where('id',$params)->first();

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
            'id_pegawai' => 'required|numeric',
            'tanggal_absen' => 'required|date',
            'waktu_absen' => 'required',
            'status' => 'required',
            'jenis' => 'required',
            'location_auth' => 'required',
            'face_auth' => 'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        $data = absen::where('id',$params)->first();
        $data->id_pegawai = $request->id_pegawai;
        $data->tanggal_absen = $request->tanggal_absen;
        $data->waktu_absen = $request->waktu_absen;
        $data->status = $request->status;
        $data->jenis = $request->jenis;
        $data->location_auth = $request->location_auth;
        $data->face_auth = $request->face_auth;
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
        $data = absen::where('id',$params)->first();
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

    public function getTime(){
        // $dt = Carbon::now()->format('h:i:sa');
        ini_set('date.timezone', 'Asia/Jakarta');
        $dt = date('Y-m-d H:i:s');
        // date_default_timezone_set('Asia/Jakarta');
       return response()->json(['time' => $dt]);
    }
}
