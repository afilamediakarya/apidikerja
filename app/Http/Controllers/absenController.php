<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\absen;
use Validator;
use Carbon\Carbon;
use Auth;
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
        $dt = date('Y-m-d H:i:s');
       return response()->json(['time' => $dt]);
    }

    public function checkAbsen(){
        $dt = date('Y-m-d');
        $time_now = date('H:i:s');
        $status_checkin = false;
        $status_checkout = false;
        $data = absen::where('id_pegawai',Auth::user()->id_pegawai)->where('tanggal_absen',$dt)->get();
        // return $data;
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
            ]);
        }else{
            return response()->json([
                  'checkin' => $status_checkin,
                  'checkout' => $status_checkout,
            ]);
        }
        

        // if ($time_now > '12:00:01') {
        //     $data = absen::where('id_pegawai',Auth::user()->id_pegawai)->where('tanggal_absen',$dt)->where('jenis','checkout')->first();
            
        //     if (isset($data)) {
        //         return response()->json([
        //             'message' => 'Anda sudah absen checkout',
        //             'status' => false,
        //         ]);
        //     }else{
        //         return response()->json([
        //             'message' => 'Anda belum absen checkout',
        //             'status' => true,
        //         ]);
        //     }

        // }else{
        //     $data = absen::where('id_pegawai',Auth::user()->id_pegawai)->where('tanggal_absen',$dt)->where('jenis','checkin')->first();
            
        //     if (isset($data)) {
        //         return response()->json([
        //             'message' => 'Anda sudah absen checkin',
        //             'status' => false,
        //         ]);
        //     }else{
        //         return response()->json([
        //             'message' => 'Anda belum absen checkin',
        //             'status' => true,
        //         ]);
        //     }

        // }
    }
}
