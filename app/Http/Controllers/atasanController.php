<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\atasan;
use App\Models\pegawai;
use App\Models\jabatan;
use Validator;
use Auth;

class atasanController extends Controller
{
    public function list(){
        $data = atasan::latest()->get();

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
            'id_penilai' => 'required|numeric',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        $checkData = atasan::where('id_pegawai',Auth::user()->id_pegawai)->first();
        $data = '';

        if (!isset($checkData)) {
            $data = new atasan();
            $data->id_penilai = $request->id_penilai;
            $data->id_pegawai = Auth::user()->id_pegawai;
            $data->save();
        }else{
            $data = atasan::where('id_pegawai',Auth::user()->id_pegawai)->first();
            $data->id_penilai = $request->id_penilai;
            $data->id_pegawai = Auth::user()->id_pegawai;
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

    public function show($params){
        $data = atasan::where('id',$params)->first();

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
            'id_penilai' => 'required|numeric'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        $data = atasan::where('id',$params)->first();
        $data->id_penilai = $request->id_penilai;
        $data->id_pegawai = Auth::user()->id_pegawai;
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
        $data = atasan::where('id',$params)->first();
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

    public function option_atasan(){
        $result = [];
        $pegawai = pegawai::where('id',Auth::user()->id_pegawai)->first();
       
        if ($pegawai) {
            $getOption = jabatan::where('id_satuan_kerja',$pegawai['id_satuan_kerja'])->get();
     
            if (isset($getOption)) {
                foreach ($getOption as $key => $value) {
                    if ($value['pegawai'] != null) {
                        $result[$key] = [
                            'id' => $value->id,
                            'value'=> $value->nama_jabatan.'-'.$value['pegawai']['nama']
                        ];
                    }else{
                        return response()->json([
                            'message' => 'Maaf, Pegawai belum mempunyai jabatan',
                            'status' => false
                        ],422);
                    }
                }
            }else{
                return response()->json([
                    'message' => 'Data tidak ada',
                    'status' => false
                ],422);
            }

        }else{
            return response()->json([
                'message' => 'Data tidak ada',
                'status' => false
            ],422);
        }
       
        return response()->json($result);
       

    }

}
