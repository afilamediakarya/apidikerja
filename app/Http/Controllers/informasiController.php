<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\informasi;
use App\Models\pegawai;
use Validator;
use Auth;
class informasiController extends Controller
{
    public function list(){
        $data = '';
        if (Auth::user()->role == 'super_admin') {
           $data = informasi::latest()->get();
        }else{
            $pegawai = pegawai::select('id_satuan_kerja')->where('id',Auth::user()->id_pegawai)->first();
            $data = informasi::where('id_satuan_kerja',$pegawai->id_satuan_kerja)->latest()->get();
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

    public function store(Request $request){
       
        $validator = Validator::make($request->all(),[
            'id_satuan_kerja' => 'required|numeric',
            'judul' => 'required|string',
            'deskripsi' => 'required|string',
            'gambar' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'tahun' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        $data = new informasi();
        $data->id_satuan_kerja = $request->id_satuan_kerja;
        $data->judul = $request->judul;
        $data->deskripsi = $request->deskripsi;
      
        if (isset($request->gambar)) {
            if ($request->hasFile('gambar')) {
                $file = $request->file('gambar');
                $filename = time().'.'.$file->getClientOriginalExtension();
                $file->storeAs('public/image',$filename);
                $data->gambar = $filename;
            }
        }
        
        $data->tahun = $request->tahun;
        $data->status = $request->status;
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
        $data = informasi::where('id',$params)->first();

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
            'id_satuan_kerja' => 'required|numeric',
            'judul' => 'required|string',
            'deskripsi' => 'required|string',
            'gambar' => 'image|mimes:jpeg,png,jpg|max:2048',
            'tahun' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        $data = informasi::where('id',$params)->first();
        $data->id_satuan_kerja = $request->id_satuan_kerja;
        $data->judul = $request->judul;
        $data->deskripsi = $request->deskripsi;
        
        if (isset($request->gambar)) {
            if ($request->hasFile('gambar')) {
                $file = $request->file('gambar');
                $filename = time().'.'.$file->getClientOriginalExtension();
                $file->storeAs('public/image',$filename);
            }
            $data->gambar = $filename;
        }
        
        $data->tahun = $request->tahun;
        $data->status = $request->status;
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
        $data = informasi::where('id',$params)->first();
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
