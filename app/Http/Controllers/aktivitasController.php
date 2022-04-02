<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\aktivitas;
use App\Models\skp;
use Validator;
use Auth;
class aktivitasController extends Controller
{
    public function list(){
        $data = aktivitas::latest()->get();

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
            'id_skp' => 'required|numeric',
            'nama_aktivitas' => 'required|string',
            'keterangan' => 'required',
            'satuan' => 'required|string',
            'waktu_awal' => 'required',
            'waktu_akhir' => 'required',
            'tanggal' => 'required|date',
            'tahun'=> 'required',
            'hasil' => 'required|numeric'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        $data = new aktivitas();
        $data->id_pegawai = $request->id_pegawai;
        $data->id_skp = $request->id_skp;
        $data->nama_aktivitas = $request->nama_aktivitas;
        $data->keterangan = $request->keterangan;
        $data->satuan = $request->satuan;
        $data->waktu_awal = $request->waktu_awal;
        $data->waktu_akhir = $request->waktu_akhir;
        $data->tanggal = $request->tanggal;
        $data->tahun = $request->tahun;
        $data->hasil = $request->hasil;
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
        $data = aktivitas::where('id',$params)->first();

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
            'id_skp' => 'required|numeric',
            'nama_aktivitas' => 'required|string',
            'keterangan' => 'required',
             'satuan' => 'required|string',
            'waktu_awal' => 'required',
            'waktu_akhir' => 'required',
            'tanggal' => 'required|date',
            'tahun'=> 'required',
            'hasil' => 'required|numeric'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        $data = aktivitas::where('id',$params)->first();
        $data->id_pegawai = $request->id_pegawai;
        $data->id_skp = $request->id_skp;
        $data->nama_aktivitas = $request->nama_aktivitas;
        $data->keterangan = $request->keterangan;
        $data->satuan = $request->satuan;
        $data->waktu_awal = $request->waktu_awal;
        $data->waktu_akhir = $request->waktu_akhir;
        $data->tanggal = $request->tanggal;
        $data->tahun = $request->tahun;
        $data->hasil = $request->hasil;
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
        $data = aktivitas::where('id',$params)->first();
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

    public function optionSkp(){
        $result = [];
        $data = skp::where('id_pegawai',Auth::user()->id_pegawai)->latest()->get();
        foreach ($data as $key => $value) {
            $result[$key] = [
                'id' => $value->id,
                'value'=> $value->rencana_kerja
            ];
        }
        return response()->json($result);
        // return collect($data)->pluck('rencana_kerja','id')->toArray();
    }
}
