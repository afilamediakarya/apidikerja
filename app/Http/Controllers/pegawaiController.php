<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\pegawai;
use App\Models\User;
use Validator;
use DB;
class pegawaiController extends Controller
{
    
    public function list(){
        $data = pegawai::latest()->get();

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
            'id_satuan_kerja' => 'required',
            'nama' => 'required|string',
            'tempat_tanggal_lahir' => 'required',
            'nip' => 'required|numeric|unique:tb_pegawai',
            'golongan_pangkat' => 'required',
            'tmt_golongan' => 'required',
            'eselon' => 'required',
            'status_pegawai' => 'required',
            'tmt_pegawai' => 'required',
            'jenis_kelamin' => 'required',
            'agama' => 'required',
            'status_perkawinan' => 'required',
            'pendidikan_akhir' => 'required',
            'no_npwp' => 'required|unique:tb_pegawai',
            'no_ktp' => 'required|unique:tb_pegawai',
            'alamat_rumah' => 'required',
            'jenis_jabatan' => 'required',
            'email' => 'required|string|email|max:255|unique:tb_pegawai'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }



        $data = new pegawai();
        $data->id_satuan_kerja = $request->id_satuan_kerja;
        $data->nama = $request->nama;
        $data->tempat_tanggal_lahir = $request->tempat_tanggal_lahir;
        $data->nip = $request->nip;
        $data->golongan_pangkat = $request->golongan_pangkat;
        $data->tmt_golongan = $request->tmt_golongan;
        $data->eselon = $request->eselon;
        $data->status_pegawai = $request->status_pegawai;
        $data->tmt_pegawai = $request->tmt_pegawai;
        $data->jenis_kelamin = $request->jenis_kelamin;
        $data->agama = $request->agama;
        $data->status_perkawinan = $request->status_perkawinan;
        $data->pendidikan_akhir = $request->pendidikan_akhir;
        $data->no_npwp = $request->no_npwp;
        $data->no_ktp = $request->no_ktp;
        $data->alamat_rumah = $request->alamat_rumah;
        $data->email = $request->email;
        $data->face_character = $request->face_character;
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

        $data = pegawai::where('id',$params)->first();

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

    public function update($params, Request $request){

        $validator = Validator::make($request->all(),[
            'id_satuan_kerja' => 'required',
            'nama' => 'required|string',
            'tempat_tanggal_lahir' => 'required',
            'nip' => 'required|numeric',
            'golongan_pangkat' => 'required',
            'tmt_golongan' => 'required',
            'eselon' => 'required',
            'status_pegawai' => 'required',
            'tmt_pegawai' => 'required',
            'jenis_kelamin' => 'required',
            'status_pegawai' => 'required',
            'agama' => 'required',
            'status_perkawinan' => 'required',
            'pendidikan_akhir' => 'required',
            'no_npwp' => 'required',
            'no_ktp' => 'required',
            'alamat_rumah' => 'required',
            'jenis_jabatan' => 'required',
            'email' => 'required|string|email|max:255'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        $data =  pegawai::where('id',$params)->first();
        $data->id_satuan_kerja = $request->id_satuan_kerja;
        $data->nama = $request->nama;
        $data->tempat_tanggal_lahir = $request->tempat_tanggal_lahir;
        $data->nip = $request->nip;
        $data->golongan_pangkat = $request->tempat_tanggal_lahir;
        $data->tmt_golongan = $request->tmt_golongan;
        $data->eselon = $request->eselon;
        $data->status_pegawai = $request->status_pegawai;
        $data->tmt_pegawai = $request->tmt_pegawai;
        $data->jenis_kelamin = $request->jenis_kelamin;
        $data->agama = $request->agama;
        $data->status_perkawinan = $request->status_perkawinan;
        $data->pendidikan_akhir = $request->pendidikan_akhir;
        $data->no_npwp = $request->no_npwp;
        $data->no_ktp = $request->no_ktp;
        $data->alamat_rumah = $request->alamat_rumah;
        if (isset($request->face_character)) {
            $data->face_character = $request->face_character;
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
        $data = pegawai::where('id',$params)->first();
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

    public function optionAgama(){
        $data = DB::table('tb_agama')->latest()->get();
        return collect($data)->pluck('nama_agama','id')->toArray();
    }

    public function optionStatusKawin(){
        $data = DB::table('tb_status_kawin')->latest()->get();
        return collect($data)->pluck('nama_status_kawin','id')->toArray();
    }

    public function optionGolongan(){
        $data = DB::table('tb_golongan')->latest()->get();
        return collect($data)->pluck('nama_golongan','id')->toArray();
    }

    public function optionStatusPegawai(){
        $data = DB::table('tb_status_pegawai')->latest()->get();
        return collect($data)->pluck('nama_status_pegawai','id')->toArray();
    }

    public function pendidikanTerakhir(){
        $data = DB::table('tb_pendidikan')->latest()->get();
        return collect($data)->pluck('nama_pendidikan','id')->toArray();
    }


}
