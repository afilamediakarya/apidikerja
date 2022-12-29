<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\bankom;
use Validator;
use Auth;
use DB;


class bankomController extends Controller
{

    public function laporan($params,$satker,$year,$id_pegawai){
        if ($params == 'rekap') {
            return $this->listByOpd($satker,$year);
        } else {
            return $this->byPegawai($id_pegawai,$year);
        }
    }

    public function listByOpd($params,$year){
        $result = [];
        $data = DB::table('tb_bankom')->select('tb_pegawai.id','tb_pegawai.nama')->join('tb_pegawai','tb_bankom.id_pegawai','=','tb_pegawai.id')->whereYear('waktu_akhir',$year)->where('tb_pegawai.id_satuan_kerja',$params)->groupBy('tb_pegawai.id')->get();

        foreach ($data as $key => $value) {
            $result[] = [
              'nama'  => $value->nama,
              'bankom' => DB::table('tb_bankom')->select('nama_pelatihan','jenis_pelatihan','waktu_awal','waktu_akhir','jumlah_jp')->where('id_pegawai',$value->id)->get(),  
            ];
        }

        return $result;
    }

    public function byPegawai($params,$year){
         $data = bankom::where('id_pegawai',$params)->whereYear('waktu_akhir',$year)->latest()->get();
         return $data;
    }

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
                $file->storeAs('http://127.0.0.1:8000/public/image',$filename);
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
