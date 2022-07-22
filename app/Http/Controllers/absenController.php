<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\absen;
use Validator;
use Carbon\Carbon;
use Auth;
use DB;
use Illuminate\Validation\ValidationException;
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

    public function list_filter_absen($satuan_kerja, $tanggal, $valid){
        // return $satuan_kerja.' / '.$tanggal.' / '.$valid;
        $pegawaiBySatuanKerja = array();
        if ($satuan_kerja == 'semua') {
            $pegawaiBySatuanKerja = DB::table('tb_pegawai')->select('tb_pegawai.id','tb_pegawai.nama')->join('tb_absen','tb_pegawai.id','=','tb_absen.id_pegawai')->where('tb_absen.tanggal_absen',$tanggal)->groupBy('tb_pegawai.id')->get();
        }else{
            $pegawaiBySatuanKerja = DB::table('tb_pegawai')->select('id','nama')->where('id_satuan_kerja',$satuan_kerja)->get();
        }

        $result = array();
        foreach ($pegawaiBySatuanKerja as $key => $value) {
           $data = array();
           $absen = DB::table('tb_absen')->select('waktu_absen','id','jenis','validation','status')->where('id_pegawai',$value->id)->where('tanggal_absen',$tanggal)->where('validation',$valid)->get();
           if (count($absen) > 0) {
            $data['id'] = $value->id;
            $data['nama_pegawai'] = $value->nama;
            foreach ($absen as $k => $val) {
                if ($val->jenis == 'checkin') {
                   $data['waktu_masuk'] = $val->waktu_absen; 
                   $data['validation'] = $val->validation;
                   $data['status'] = $val->status; 
                   $data['tanggal_absen'] = $val->tanggal_absen; 
                }else{
                    $data['waktu_pulang'] = $val->waktu_absen;
                }
            }

            $result[] = $data;

           }
          

        }

         if ($result) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $result
            ]);
        }else{
            return response()->json([
                'message' => 'Failed',
                'status' => false,
                'data' => $result
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
        $data->user_update = $request->user_update;
        $data->validation = $request->validation;
        $data->tahun = date('Y');
        $data->save();

        if ($data) {
            return response()->json([
                'message'     => 'Success',
                'status' => true,
                'data' => $data
            ]);
        }else{
            // return response()->json([
            //     'message' => 'Failed',
            //     'status' => false
            // ],422);
              throw ValidationException::withMessages([
                    'message' => ['Gagal.'],
             ]);
        }
    }

    // public function store_(Request )

    public function show($pegawai, $tanggal,$valid){
        $data = array();
        $satker = DB::table('tb_pegawai')->select('id_satuan_kerja')->where('id',$pegawai)->first();
        $absen = DB::table('tb_absen')->select('waktu_absen','id','jenis','validation','status')->where('id_pegawai',$pegawai)->where('tanggal_absen',$tanggal)->where('validation',$valid)->get();

                   if (count($absen) > 0) {
            $data['id_pegawai'] = $pegawai;
            $data['id_satuan_kerja'] = $satker->id_satuan_kerja;
            foreach ($absen as $k => $val) {
                if ($val->jenis == 'checkin') {
                   $data['waktu_masuk'] = $val->waktu_absen; 
                   $data['validation'] = $val->validation;
                   $data['status'] = $val->status; 
                }else{
                    $data['waktu_pulang'] = $val->waktu_absen;
                }
            }
           }

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
        $dt = date('Y-m-d H:i:s');
       return response()->json(['time' => $dt]);
    }

    public function checkAbsen(){
        $status_ = '';
        $dt = date('Y-m-d');
        $time_now = date('H:i:s');
        $status_checkin = false;
        $status_checkout = false;
        $data = absen::where('id_pegawai',Auth::user()->id_pegawai)->where('tanggal_absen',$dt)->get();
        // return $data;
        if (count($data) > 0) {
            $status_ = $data[0]['status'];
        }

        if (isset($data)) {
          
            foreach ($data as $key => $value) {
                if ($value['jenis'] == 'checkin') {
                    $status_checkin = true;
                }else{
                    $status_checkout = true;
                }

            }
                        
            return response()->json([
                'checkin' => $status_checkin,
                'checkout' => $status_checkout,
                'status' => $status_
            ]);
        }else{
            return response()->json([
                  'checkin' => $status_checkin,
                  'checkout' => $status_checkout,
                  'status' => $status_
            ]);
        }
    }
}
