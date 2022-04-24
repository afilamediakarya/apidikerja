<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\kegiatan;
use App\Models\pegawai;
use Validator;
use Auth;
class kegiatanController extends Controller
{
    public function list(){
        $data = '';
        if (Auth::user()->role == 'super_admin') {
            $data = kegiatan::latest()->get();   
        }else{
             $pegawai = pegawai::select('id_satuan_kerja')->where('id',Auth::user()->id_pegawai)->first();
                $data = kegiatan::where('id_satuan_kerja',$pegawai->id_satuan_kerja)->latest()->get();
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
            'nama_kegiatan' => 'required|string',
            'tahun' => 'required|string',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        $data = new kegiatan();
        $data->id_satuan_kerja	 = $request->id_satuan_kerja;
        $data->nama_kegiatan = $request->nama_kegiatan;
        $data->tahun = $request->tahun;
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
        $data = kegiatan::where('id',$params)->first();

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
            'nama_kegiatan' => 'required|string',
            'tahun' => 'required|string',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        $data = kegiatan::where('id',$params)->first();
        $data->id_satuan_kerja	 = $request->id_satuan_kerja;
        $data->nama_kegiatan = $request->nama_kegiatan;
        $data->tahun = $request->tahun;
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
        $data = kegiatan::where('id',$params)->first();
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
