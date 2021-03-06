<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\absen;
use Validator;
use Carbon\Carbon;
use Auth;
use DB;
use Illuminate\Support\Str;
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

    public function list_filter_absen(){
        
        $satuan_kerja = request('satuan_kerja');
        $tanggal = request('tanggal');
        $valid = request('valid');
        $status = request('status');

        $pegawaiBySatuanKerja = array();
        $result = array();
        $absen = array();

        $where = '';
        if ($valid !== 'semua') {
            $where .= "AND validation='$valid'";
        }

        if ($status !== 'semua') {
            $where .= "AND status='$status'";
        }
        

        if ($satuan_kerja !== 'semua') {
            $pegawaiBySatuanKerja = DB::table('tb_pegawai')->select('id','nama')->where('id_satuan_kerja',$satuan_kerja)->get();

            foreach ($pegawaiBySatuanKerja as $key => $value) {
                $data = array();
                

                $absen = DB::table('tb_absen')->select('waktu_absen','id','jenis','validation','status','tanggal_absen')->where('id_pegawai',$value->id)->whereRaw("tanggal_absen = '$tanggal' $where")->get();      
    //   if ($valid == 'semua') {
    //                 $absen = DB::table('tb_absen')->select('waktu_absen','id','jenis','validation','status','tanggal_absen')->where('id_pegawai',$value->id)->where('tanggal_absen',$tanggal)->get();
    //              }else{
    //                  $absen = DB::table('tb_absen')->select('waktu_absen','id','jenis','validation','status','tanggal_absen')->where('id_pegawai',$value->id)->where('tanggal_absen',$tanggal)->where('validation',$valid)->get();
    //              }
           
     
                 
                 
     
                if (count($absen) > 0) {
                 $data['id'] = $value->id;
                 $data['nama_pegawai'] = $value->nama;
                 $data['waktu_pulang'] = '-';
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
     
                 
                 if (array_key_exists('waktu_masuk', $data) == true) {
                     $result[] = $data;
                 }
              
     
                }
               
     
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

        $status = $request->status;
        $validation_ = 0;
        $user_update = '';
        if (!isset($request->validation)) {
            $user_update = Auth::user()->id_pegawai;
            if ($request->jenis == 'checkout') {
                $check_absen = json_decode($this->checkAbsen()->content(), true)['status'];
                if ($check_absen == 'hadir' || $check_absen == 'apel') {
                    $validation_ = 1;
                }
                $status = 'hadir';
                
            }else{
                if ($status == 'hadir' || $status == 'apel') {
                    $validation_ = 1;
                }elseif ($status == 'dinas luar' || $status == 'izin' || $status == 'sakit') {
                    $validation_ = 0;
                }
            
            }

        }else{
            $validation_ = $request->validation;
            $user_update = $request->user_update;
        }

        $data = new absen();
        $data->id_pegawai = $request->id_pegawai;
        $data->tanggal_absen = $request->tanggal_absen;
        $data->waktu_absen = $request->waktu_absen;
        $data->status = $status;
        $data->jenis = $request->jenis;
        $data->location_auth = $request->location_auth;
        $data->face_auth = $request->face_auth;
        $data->user_update = $user_update;
        $data->validation = $validation_;
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
        $absen = DB::table('tb_absen')->select('waktu_absen','id','jenis','validation','status','tanggal_absen')->where('id_pegawai',$pegawai)->where('tanggal_absen',$tanggal)->where('validation',$valid)->get();

          if (count($absen) > 0) {
            $data['satker'] = [
                'satuan_kerja' => $satker->id_satuan_kerja,
                'id_pegawai' => $pegawai
            ];
            foreach ($absen as $k => $val) {
                if ($val->jenis == 'checkin') {
                   $data['waktu_absen_masuk'] = $val->waktu_absen; 
                   $data['validation'] = $val->validation;
                   $data['status'] = Str::slug($val->status,'_'); 
                   $data['tanggal'] = $val->tanggal_absen; 
                }else{
                    $data['waktu_absen_pulang'] = $val->waktu_absen;
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
        // return $request->all();
        $data = array();
        $absen = DB::table('tb_absen')->where('tanggal_absen',$request->tanggal_absen)->where('id_pegawai',$params)->get();

        foreach ($absen as $key => $value) {
            $data = absen::where('id',$value->id)->first();
            $data->id_pegawai = $request->id_pegawai;
            $data->tanggal_absen = $request->tanggal_absen;
            if ($value->jenis == 'checkin') {
                $data->waktu_absen = $request->waktu_absen_masuk;
                $data->status = $request->status; 
            }else{
                $data->waktu_absen = $request->waktu_absen_pulang;
            }
            $data->save();   
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

    public function absenCheckAdmin($id_pegawai,$tanggal_absen){
        $status_ = '';
        $status_checkin = false;
        $status_checkout = false;
        $data = absen::where('id_pegawai',$id_pegawai)->where('tanggal_absen',$tanggal_absen)->get();
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

    public function change_validation(Request $request){

         $absen = DB::table('tb_absen')->where('tanggal_absen',$request->tanggal)->where('id_pegawai',$request->id_pegawai)->get();
         foreach ($absen as $key => $value) {

             $validation_ = DB::table('tb_absen')
              ->where('id', $value->id)
              ->update(['validation' => $request->valid]);
         }

        if ($validation_) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $validation_
            ]);
        }else{
            return response()->json([
                'message' => 'Failed',
                'status' => false
            ]);
        }
    }

}
