<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\pegawai;
use App\Models\absen;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Validator;
use DB;
use Auth;
class pegawaiController extends Controller
{
    
    public function list(){
        $data = '';
        if (Auth::user()->role == 'admin') {
            $data = pegawai::latest()->get();
        }else{
            $pegawai = pegawai::where('id',Auth::user()->id_pegawai)->first();
            if (isset($pegawai)) {
                $data = pegawai::where('id_satuan_kerja',$pegawai['id_satuan_kerja'])->latest()->get();
            }else{
                $data = [];
            }
           
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
            'id_satuan_kerja' => 'required',
            'nama' => 'required|string',
            'tempat_lahir' => 'required',
            'tanggal_lahir' => 'required|date',
            'nip' => 'required|numeric|unique:tb_pegawai',
            'golongan' => 'required',
            'tmt_golongan' => 'required|date',
            'eselon' => 'required',
            'tmt_pegawai' => 'required|date',
            'jenis_kelamin' => 'required',
            'agama' => 'required',
            'status_perkawinan' => 'required',
            'pendidikan' => 'required',
            'lulus_pendidikan' => 'required',
            'pendidikan_struktural' => 'required',
            'lulus_pendidikan_struktural' => 'required',
            'jurusan' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }



        $data = new pegawai();
        $data->id_satuan_kerja = $request->id_satuan_kerja;
        $data->nama = $request->nama;
        $data->tempat_lahir = $request->tempat_lahir;
        $data->tanggal_lahir = $request->tanggal_lahir;
        $data->nip = $request->nip;
        $data->golongan = $request->golongan;
        $data->tmt_golongan = $request->tmt_golongan;
        $data->eselon = $request->eselon;
        $data->jenis_jabatan = $request->jenis_jabatan;
        $data->tmt_pegawai = $request->tmt_pegawai;
        $data->jenis_kelamin = $request->jenis_kelamin;
        $data->agama = $request->agama;
        $data->status_perkawinan = $request->status_perkawinan;
        $data->pendidikan = $request->pendidikan;
        $data->lulus_pendidikan = $request->lulus_pendidikan;
        $data->pendidikan_struktural = $request->pendidikan_struktural;
        $data->lulus_pendidikan_struktural = $request->lulus_pendidikan_struktural;
        $data->jurusan = $request->jurusan;
        $data->face_character = $request->face_character;
        $data->save();

        $user = new User();
        $user->username = $data->nip;
        $user->password = Hash::make('dikerja');
        $user->id_pegawai = $data->id;
        $user->role = 'pegawai';
        $user->save();


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
            'tempat_lahir' => 'required',
            'tanggal_lahir' => 'required|date',
            'nip' => 'required|numeric',
            'golongan' => 'required',
            'tmt_golongan' => 'required',
            'eselon' => 'required',
            'tmt_pegawai' => 'required|date',
            'jenis_kelamin' => 'required',
            'agama' => 'required',
            'status_perkawinan' => 'required',
            'pendidikan' => 'required',
            'lulus_pendidikan' => 'required',
            'pendidikan_struktural' => 'required',
            'lulus_pendidikan_struktural' => 'required',
            'jurusan' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        $data =  pegawai::where('id',$params)->first();
        $data->id_satuan_kerja = $request->id_satuan_kerja;
        $data->nama = $request->nama;
        $data->tempat_lahir = $request->tempat_lahir;
        $data->tanggal_lahir = $request->tanggal_lahir;
        $data->nip = $request->nip;
        $data->golongan = $request->golongan;
        $data->tmt_golongan = $request->tmt_golongan;
        $data->eselon = $request->eselon;
        $data->jenis_jabatan = $request->jenis_jabatan;
        $data->tmt_pegawai = $request->tmt_pegawai;
        $data->jenis_kelamin = $request->jenis_kelamin;
        $data->agama = $request->agama;
        $data->status_perkawinan = $request->status_perkawinan;
        $data->pendidikan = $request->pendidikan;
        $data->lulus_pendidikan = $request->lulus_pendidikan;
        $data->pendidikan_struktural = $request->pendidikan_struktural;
        $data->lulus_pendidikan_struktural = $request->lulus_pendidikan_struktural;
        $data->jurusan = $request->jurusan;
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
        $result = [];
        $data = DB::table('tb_agama')->latest()->get();
        foreach ($data as $key => $value) {
            $result[$key] = [
                'value'=> $value->nama_agama
            ];
        }
        return response()->json($result);
    }

    public function optionStatusKawin(){
        $result = [];
        $data = DB::table('tb_status_kawin')->latest()->get();
        foreach ($data as $key => $value) {
            $result[$key] = [
                'value'=> $value->nama_status_kawin
            ];
        }
        return response()->json($result);
    }

    public function optionGolongan(){
        $result = [];
        $data = DB::table('tb_golongan')->latest()->get();
        foreach ($data as $key => $value) {
            $result[$key] = [
                'value'=> $value->nama_golongan
            ];
        }
        return response()->json($result);
    }

    public function optionStatusPegawai(){
        $result = [];
        $data = DB::table('tb_status_pegawai')->latest()->get();
        foreach ($data as $key => $value) {
            $result[$key] = [
                'value'=> $value->nama_status_pegawai
            ];
        }
        return response()->json($result);
    }

    public function pendidikanTerakhir(){
        $result = [];
        $data = DB::table('tb_pendidikan')->latest()->get();
        foreach ($data as $key => $value) {
            $result[$key] = [
                'value'=> $value->nama_pendidikan
            ];
        }
        return response()->json($result);
    }

    public function optionEselon(){
        $result = [];
        $data = DB::table('tb_eselon')->latest()->get();
        foreach ($data as $key => $value) {
            $result[$key] = [
                'value'=> $value->nama_eselon
            ];
        }
        return response()->json($result);
    }

    public function tesScheduling(){
        $dt = date('Y-m-d');
        $absen = '';
        $data = DB::table('tb_pegawai')->whereNotExists(function($query){
            $query->select(DB::raw(1))->from('tb_absen')->whereColumn('tb_absen.id_pegawai','tb_pegawai.id');
        })->get();

        foreach ($data as $key => $value) {
            for ($i=0; $i < 2; $i++) { 
                $absen = new absen();
                $absen->id_pegawai = $value->id;
                $absen->tanggal_absen = $dt;
                $absen->status = 'alpa';
                if ($i == 0) {
                    $absen->jenis = 'checkin';
                    $absen->waktu_absen = '08:00:00';
                }else{
                    $absen->jenis = 'checkout';
                    $absen->waktu_absen = '17:00:00';
                }
                $absen->location_auth = 'valid';
                $absen->face_auth = 'valid';
                $absen->tahun = '2022';
                $absen->save();   
            }
        }

    
      
    }


}
