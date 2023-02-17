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
use Illuminate\Support\Facades\Redis;
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

    public function checkAbsenToday(){
        $data = DB::table('tb_absen')->select('status')->where('id_pegawai',Auth::user()->id_pegawai)->where('tanggal_absen',date('Y-m-d'))->first();

         if ($data) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $data
            ]);
        }else{
            return response()->json([
                'message' => 'Failed',
                'status' => false,
                'data' => $data
            ]);
        }    
    }

      public function checkAbsenbyDate(){
        // return date('Y-m')
        $data = '';

        $getDataCache= Redis::get('checkAbsenbyDate_'.Auth::user()->id_pegawai);
		$data = json_decode($getDataCache);

        if (!$getDataCache) {
            $date = request('tanggal');
            if (date('D', strtotime($date)) == 'Sun') {
                $data = null;
            }else{
                $data = DB::table('tb_absen')->select('status')->where('id_pegawai',Auth::user()->id_pegawai)->where('tanggal_absen',$date)->first();
            }
            Redis::set('checkAbsenbyDate_'.Auth::user()->id_pegawai, json_encode($data));
            Redis::expire('checkAbsenbyDate_'.Auth::user()->id_pegawai, 1800);
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
                'status' => false,
                'data' => $data
            ]);
        }    
    }

    public function list_filter_absen(){
        $result = array();
        $satuan_kerja = request('satuan_kerja');
        $getDataCache= Redis::get('list_filter_absen_'.$satuan_kerja);
		$result = json_decode($getDataCache);

        if (!$getDataCache) {
            
            $tanggal = request('tanggal');
            $valid = request('valid');
            $status = request('status');

            $pegawaiBySatuanKerja = array();
        
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

                Redis::set('list_filter_absen_'.$satuan_kerja, json_encode($data));
                Redis::expire('list_filter_absen_'.$satuan_kerja, 1800);

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
        DB::connection()->enableQueryLog();
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

        $queries = DB::getQueryLog();
        $executionTime = $queries[count($queries) - 1]['time'];

        \Log::info("Query execution time: $executionTime");

        if ($request->status == 'izin' || $request->status == 'sakit' || $request->status == 'cuti') {
            $this->generateCheckout($request);
        }

        if ($data) {
            return response()->json([
                'message'     => 'Success',
                'status' => true,
                'data' => $data,
                'excute_time' => $executionTime
            ]);
        }else{
              throw ValidationException::withMessages([
                    'message' => ['Gagal.'],
             ]);
        }
    }

    public function generateCheckout($request){
        $data = new absen();
        $data->id_pegawai = $request->id_pegawai;
        $data->tanggal_absen = $request->tanggal_absen;
        $data->waktu_absen = '16:00:00';
        $data->status = 'hadir';
        $data->jenis = 'checkout';
        $data->location_auth = $request->location_auth;
        $data->face_auth = $request->face_auth;
        $data->user_update = $request->user_update;
        $data->validation = $request->validation;
        $data->tahun = date('Y');
        $data->save();
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
        $result = array();

        $getDataCache= Redis::get('checkAbsen_'.Auth::user()->id_pegawai);
        $result = json_decode($getDataCache);

        if (!$getDataCache) {
            $status_ = '';
            $dt = date('Y-m-d');
            $time_now = date('H:i:s');
            $status_checkin = false;
            $status_checkout = false;
            $data = absen::select('jenis','status')->where('id_pegawai',Auth::user()->id_pegawai)->where('tanggal_absen',$dt)->get();
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
            }

            $result = ['checkin' => $status_checkin,'checkout' => $status_checkout,'status' => $status_];
            Redis::set('checkAbsen_'.Auth::user()->id_pegawai, json_encode($result));
            Redis::expire('checkAbsen_'.Auth::user()->id_pegawai, 1800);
        }

       

        return response()->json($result);
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
