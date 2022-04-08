<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\aktivitas;
use App\Models\skp;
use Validator;
use Auth;
use DB;

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

    public function convertNamaBulan($params){
        switch ($params) {
            case '1':
              return 'Januari';
            case '2':
              return 'Februari';
            case '3':
              return 'Maret';
            case '4':
              return 'April';
            case '5':
              return 'Mei';
            case '6':
              return 'Juni';
            case '7':
              return 'Juli';
            case '8':
                return 'Juli';
            case '9':
                return 'Agustus';
            case '10':
                return 'September';
            case '11':
                return 'November';
            default:
              return 'Desember';
          }
    }

    public function listByUser(){
 
        $result = [];
      
       $getbulan = DB::table("tb_aktivitas")->select(DB::raw('EXTRACT(MONTH FROM tanggal) AS bulan'))->where('id_pegawai',Auth::user()->id_pegawai)->groupBy('bulan')->get();

        $bulan = '';

        foreach ($getbulan as $key => $value) {
            $aktivitas = [];
            $aktivitas_data_date = [];
            // return $value;
            $bulan = $this->convertNamaBulan($value->bulan);

            $aktivitasgetDate = aktivitas::select(DB::raw('tanggal as date'))->whereMonth('tanggal',$value->bulan)->groupBy('date')->orderBy('date')->get();
            // foreach ($aktivitasgetDate as $x => $y) {
            //     $getAktivitas = aktivitas::where('tanggal',$y->date)->get();
            //     $aktivitas[$x] = $getAktivitas;
            // }

            // $result[$key] = [
            //     'tanggal' => $y->date,
            //     'aktivitas' => $aktivitas
            // ];

            // TESTING
            foreach ($aktivitasgetDate as $x => $y) {
                $getAktivitas = aktivitas::where('tanggal',$y->date)->get();
                // $aktivitas[$y->date][$x] = $getAktivitas;
            
                // array_push($aktivitas_data_date,$getAktivitas);
                $aktivitas[$x] = [
                    'tanggal' =>$y->date,
                    'data_tanggal'=>$getAktivitas 
                ];
            }

            // $result[$key][$bulan][] = $aktivitas;
            $result[$key] = [
                'bulan'=>$bulan,
                'data_bulan'=>$aktivitas
            ];

          
        }

        if ($result) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $result
            ]);
        }else{
            return response()->json([
                'message' => 'Aktivitas belum ada',
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
        $data->id_pegawai = Auth::user()->id_pegawai;
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
        $data->id_pegawai =Auth::user()->id_pegawai;
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
