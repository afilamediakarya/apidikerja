<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\bankom;
use Validator;
use Auth;
use DB;


class bankomController extends Controller
{
    public function list(){
        $data = bankom::where('id_pegawai',Auth::user()->id_pegawai)->latest()->get();

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
            'nama_pelatihan' => 'required|string',
            'jenis_pelatihan' => 'required',
            'jumlah_jp' => 'required|numeric',
              'waktu_awal' => 'required',
            'waktu_akhir' => 'required',
            'sertifikat' => 'required|mimes:pdf|max:2048'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(),422);       
        }

         $data = new bankom();
        $data->id_pegawai = Auth::user()->id_pegawai;
        $data->nama_pelatihan = $request->nama_pelatihan;
        $data->jenis_pelatihan = $request->jenis_pelatihan;
        $data->jumlah_jp = $request->jumlah_jp;
        $data->waktu_awal = $request->waktu_awal;
         $data->waktu_akhir = $request->waktu_akhir;
        if (isset($request->sertifikat)) {
            if ($request->hasFile('sertifikat')) {
                $file = $request->file('sertifikat');
                $filename = time().'.'.$file->getClientOriginalExtension();
                $file->storeAs('public/image',$filename);
                $data->sertifikat = $filename;
            }
        }
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
        $data = bankom::where('id',$params)->first();

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
            'nama_pelatihan' => 'required|string',
            'jenis_pelatihan' => 'required',
            'jumlah_jp' => 'required|numeric',
            'waktu_awal' => 'required',
            'waktu_akhir' => 'required',
            'sertifikat' => 'mimes:pdf|max:2048'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        $data = bankom::where('id',$params)->first();
        $data->id_pegawai = Auth::user()->id_pegawai;
        $data->nama_pelatihan = $request->nama_pelatihan;
        $data->jenis_pelatihan = $request->jenis_pelatihan;
        $data->jumlah_jp = $request->jumlah_jp;
        $data->waktu_awal = $request->waktu_awal;
         $data->waktu_akhir = $request->waktu_akhir;

        
        if (isset($request->gambar)) {
            if ($request->hasFile('sertifikat')) {
                $file = $request->file('sertifikat');
                $filename = time().'.'.$file->getClientOriginalExtension();
                $file->storeAs('public/image',$filename);
                $data->sertifikat = $filename;
            }
        }
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
        $data = bankom::where('id',$params)->first();
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
