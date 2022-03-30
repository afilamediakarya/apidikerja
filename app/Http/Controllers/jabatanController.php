<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\jabatan;
class jabatanController extends Controller
{
    public function list(){
        $data = jabatan::latest()->get();

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
            'id_pegawai' => 'required|numeric',
            'id_satuan_kerja' => 'required|numeric',
            'id_kelas_jabatan' => 'required|numeric',
            'parent_id' => 'required|numeric',
            'nama_struktur' => 'required',
            'nama_jabatan' => 'required',
            'level' => 'required|numeric',
            'status_jabatan' => 'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $filename = time().'.'.$file->getClientOriginalExtension();
            $file->storeAs('public/image',$filename);
        }

        $data = new jabatan();
        $data->id_pegawai = $request->id_pegawai;
        $data->id_satuan_kerja = $request->id_satuan_kerja;
        $data->id_kelas_jabatan = $request->id_kelas_jabatan;
        $data->parent_id = $request->parent_id;
        $data->nama_struktur = $request->nama_struktur;
        $data->nama_jabatan = $request->nama_jabatan;
        $data->level = $request->level;
        $data->status_jabatan = $request->status_jabatan;
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
        $data = jabatan::where('id',$params)->first();

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
            'id_satuan_kerja' => 'required|numeric',
            'id_kelas_jabatan' => 'required|numeric',
            'parent_id' => 'required|numeric',
            'nama_struktur' => 'required',
            'nama_jabatan' => 'required',
            'level' => 'required|numeric',
            'status_jabatan' => 'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        $data = jabatan::where('id',$params)->first();
        $data->id_pegawai = $request->id_pegawai;
        $data->id_satuan_kerja = $request->id_satuan_kerja;
        $data->id_kelas_jabatan = $request->id_kelas_jabatan;
        $data->parent_id = $request->parent_id;
        $data->nama_struktur = $request->nama_struktur;
        $data->nama_jabatan = $request->nama_jabatan;
        $data->level = $request->level;
        $data->status_jabatan = $request->status_jabatan;
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
        $data = jabatan::where('id',$params)->first();
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