<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\lokasi;
use Validator;
use Auth;
use DB;
class lokasiController extends Controller
{

    public function optionLokasi(){
        $data = '';
        if (Auth::user()->role == 'admin_opd') {
             $data = lokasi::select('id','nama_lokasi')->where('id_satuan_kerja',Auth::user()->pegawai['id_satuan_kerja'])->latest()->get();
        }else{
             $data = lokasi::select('id','nama_lokasi')->latest()->get();
        }
       
        return response()->json($data);
    }

    public function list(){
         $data = lokasi::with('satuan_kerja')->latest()->get();

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
            'satuan_kerja' => 'required|numeric',
            'nama_lokasi' => 'required',
            'longitude' => 'required',
            'lattitude' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(),422);       
        }

        $data = new lokasi;
        $data->id_satuan_kerja = $request->satuan_kerja;
        $data->nama_lokasi = $request->nama_lokasi;
        $data->long = $request->longitude;
        $data->lat = $request->lattitude;
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
        // return $params;
        $data = lokasi::where('id',$params)->first();
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

    public function update($params,Request $request){

        $validator = Validator::make($request->all(),[
             'satuan_kerja' => 'required|numeric',
            'nama_lokasi' => 'required',
            'longitude' => 'required',
            'lattitude' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(),422);       
        }

        $data = lokasi::where('id',$params)->first();
        $data->id_satuan_kerja = $request->satuan_kerja;
        $data->nama_lokasi = $request->nama_lokasi;
        $data->long = $request->longitude;
        $data->lat = $request->lattitude;
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
        $data = lokasi::where('id',$params)->first();
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
