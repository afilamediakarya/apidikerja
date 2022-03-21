<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\informasi;
use Validator;
class informasiController extends Controller
{
    public function list(){
        $data = informasi::latest()->get();

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
            'gambar' => 'required|image',
            'tahun' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $filename = time().'.'.$file->getClientOriginalExtension();
            $file->storeAs('public/image',$filename);
        }

        $data = new informasi();
        $data->id_satuan_kerja = $request->id_satuan_kerja;
        $data->judul = $request->judul;
        $data->deskripsi = $request->deskripsi;
        $data->gambar = $filename;
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
            'gambar' => 'image',
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
