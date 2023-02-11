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
    public function list()
    {
        $data = aktivitas::where('id_pegawai', Auth::user()->id_pegawai)->latest()->get();

        if ($data) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $data
            ]);
        } else {
            return response()->json([
                'message' => 'Failed',
                'status' => false
            ]);
        }
    }

    public function listByDate()
    {
        $data = aktivitas::with('skp')->where('id_pegawai', Auth::user()->id_pegawai)->where('tanggal',request('tanggal'))->latest()->get();

        if ($data) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $data
            ]);
        } else {
            return response()->json([
                'message' => 'Failed',
                'status' => false
            ]);
        }
    }

    public function convertNamaBulan($params)
    {
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

    public function listByUser()
    {
        // DB::connection()->enableQueryLog();
        $result = [];

        $getbulan = DB::table("tb_aktivitas")->select(DB::raw('EXTRACT(MONTH FROM tanggal) AS bulan'))->where('id_pegawai', Auth::user()->id_pegawai)->groupBy('bulan')->get();

        $bulan = '';
        // $aktivitasgetDate = array();

        foreach ($getbulan as $key => $value) {
            $aktivitas = [];
            $aktivitas_data_date = [];
            // return $value;
            $bulan = $this->convertNamaBulan($value->bulan);

            $aktivitasgetDate = aktivitas::select(DB::raw('tanggal as date'),'id','nama_aktivitas','tanggal','hasil','keterangan')->whereMonth('tanggal', $value->bulan)->where('id_pegawai',Auth::user()->id_pegawai)->groupBy('date')->orderBy('date')->get();

            $result[$key] = [
                'bulan' => $bulan,
                'data_bulan' => $aktivitasgetDate
            ];
        }

        // $queries = DB::getQueryLog();
        // $executionTime = $queries[count($queries) - 1]['time'];

        // \Log::info("Query execution time: $executionTime");

        if ($result) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $result,
            ]);
        } else {
            return response()->json([
                'message' => 'Aktivitas belum ada',
                'status' => false
            ]);
        }
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'id_skp' => 'required|numeric',
            'nama_aktivitas' => 'required|string',
            'keterangan' => 'required',
            'satuan' => 'required|string',
            'tanggal' => 'required|date',
            'hasil' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $waktu = 0;
        $jumlah_kinerja = $this->checkMenitKinerja($request->tanggal)->getData();
        $ax = $request->waktu + $jumlah_kinerja->data->count;

        // return $ax;

        if ($ax > 420) {
            $n_ = (420 - $jumlah_kinerja->data->count) - $request->waktu;
            $waktu = $ax + $n_;
            $waktu = $waktu - $jumlah_kinerja->data->count;  

            if ($waktu == 0) {
                return response()->json([
                    'error' => [
                        'title' =>'Jumlah waktu sudah cukup',
                        'text' => 'anda tidak bisa menambah aktivitas'
                    ]
                ], 422);
            }

            //   return response()->json(['error'=> [
            //     'text' => 'Anda belum bisa menambah aktivitas',
            //     'title' => 'Maaf Anda belum Absen'
            // ] ],422);
        }else{
            $waktu = $request->waktu;
        }

        $data = new aktivitas();
        $data->id_pegawai = Auth::user()->id_pegawai;
        $data->id_skp = $request->id_skp;
        $data->nama_aktivitas = $request->nama_aktivitas;
        $data->keterangan = $request->keterangan;
        $data->satuan = $request->satuan;
        $data->waktu_awal = '12:02:00';
        $data->waktu_akhir = '12:02:00';        
        $data->tanggal = $request->tanggal;
        $data->tahun = date('Y');
        $data->hasil = $request->hasil;
        $data->waktu = $waktu;
        $data->satuan = $request->satuan;
        $data->jenis = $request->jenis;
        $data->save();


        if ($data) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $data
            ]);
        } else {
            return response()->json([
                'message' => 'Failed',
                'status' => false
            ]);
        }
    }

    public function show($params)
    {
                $data = aktivitas::where('id', $params)->first();

        $tgl1 = strtotime($data->tanggal); 
        $tgl2 = strtotime(date('Y-m-d')); 

        $jarak = $tgl2 - $tgl1;

        $range = $jarak / 60 / 60 / 24;

        $data->range = $range;

        if ($data) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $data
            ]);
        } else {
            return response()->json([
                'message' => 'Failed',
                'status' => false
            ]);
        }
    }

    public function update($params, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_skp' => 'required|numeric',
            'nama_aktivitas' => 'required|string',
            'keterangan' => 'required',
            'satuan' => 'required|string',
            'tanggal' => 'required|date',
            'hasil' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $data = aktivitas::where('id', $params)->first();
        $data->id_pegawai = Auth::user()->id_pegawai;
        $data->id_skp = $request->id_skp;
        $data->nama_aktivitas = $request->nama_aktivitas;
        $data->keterangan = $request->keterangan;
        $data->satuan = $request->satuan;
            $data->waktu_awal = '12:02:00';
        $data->waktu_akhir = '12:02:00';                
        $data->tanggal = $request->tanggal;
        $data->tahun = date('Y');
        $data->hasil = $request->hasil;
        $data->waktu = $request->waktu;
        $data->satuan = $request->satuan;
        $data->jenis = $request->jenis;

        $data->save();

        if ($data) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $data
            ]);
        } else {
            return response()->json([
                'message' => 'Failed',
                'status' => false
            ]);
        }
    }

    public function delete($params)
    {
        $data = aktivitas::where('id', $params)->first();
        $data->delete();

        if ($data) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
            ]);
        } else {
            return response()->json([
                'message' => 'Failed',
                'status' => false
            ]);
        }
    }

    public function optionSkp()
    {
        $result = [];
        $data = array();
        $tahun = request('tahun');

        $jabatanByPegawai =  DB::table('tb_jabatan')->select('tb_jabatan.id', 'tb_jabatan.id_pegawai')->join('tb_pegawai', 'tb_jabatan.id_pegawai', '=', 'tb_pegawai.id')->where('tb_jabatan.id_pegawai', Auth::user()->id_pegawai)->first();

        if (isset($tahun)) {
            // $data = skp::where('id_jabatan',$jabatanByPegawai->id)->latest()->where('tahun',$tahun)->get();
            $data = skp::where('id_jabatan', $jabatanByPegawai->id)
                ->where('tahun', $tahun)
                ->orderBy('jenis', 'ASC')
                ->orderBy('id_skp_atasan', 'ASC')
                ->orderBy('id', 'ASC')
                ->get();
        } else {
            // $data = skp::where('id_jabatan', $jabatanByPegawai->id)->latest()->where('tahun', date('Y'))->get();
            $data = skp::where('id_jabatan', $jabatanByPegawai->id)
                ->where('tahun', date('Y'))
                ->orderBy('jenis', 'ASC')
                ->orderBy('id_skp_atasan', 'ASC')
                ->orderBy('id', 'ASC')
                ->get();
        }

        foreach ($data as $key => $value) {
            $result[$key] = [
                'id' => $value->id,
                'value' => $value->rencana_kerja
            ];
        }
        return response()->json($result);
        // return collect($data)->pluck('rencana_kerja','id')->toArray();
    }

    public function checkMenitKinerja($params){
        $data = aktivitas::select(DB::raw("SUM(waktu) as count"))->where('id_pegawai',Auth::user()->id_pegawai)->where('tanggal',$params)->first();
        if($data->count == null){
            $data->count = 0;
        }

        if ($data) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $data
            ]);
        } else {
            return response()->json([
                'message' => 'Data belum ada',
                'status' => false,
                'data' => $data
            ]);
        }
    }

    public function review_aktivitas_list(){
        $bulan = request('bulan');
        $data = array();
        $nilai_kinerja = 0;
        $capaian_menit = 0;
        $target_nilai = 0;
        $jabatanPegawai = DB::table('tb_jabatan')->select('id')->where('id_pegawai', Auth::user()->id_pegawai)->first();
        if (isset($jabatanPegawai)) {
            $myArray = DB::table('tb_jabatan')->select('tb_jabatan.id', 'tb_jabatan.id_pegawai', 'tb_jabatan.nama_jabatan', 'tb_pegawai.nama', 'tb_pegawai.nip','tb_jabatan.target_waktu','tb_jabatan.kelas_jabatan')->join('tb_pegawai', 'tb_jabatan.id_pegawai', '=', 'tb_pegawai.id')->where('parent_id', $jabatanPegawai->id)->get();

            foreach ($myArray as $key => $value) {
            $aktivitas = DB::table('tb_aktivitas')->select('id','id_pegawai','hasil',DB::raw("SUM(waktu) as count"))->whereMonth('tanggal',$bulan)->where('id_pegawai',$value->id_pegawai)->whereNotNull('id_pegawai')->get();  

            count($aktivitas) > 0 ? $capaian_menit = $aktivitas[0]->count : $capaian_menit = 0;
            $value->target_waktu !== null ? $target_nilai = $value->target_waktu : $target_nilai = 0;
            
            if ($value->kelas_jabatan == 1 || $value->kelas_jabatan == 3 || $value->kelas_jabatan == 15) {
                $nilai_kinerja = 100;
            }else{
                if ($target_nilai > 0) {
                    $nilai_kinerja = ( $capaian_menit / $target_nilai ) * 100;
                }else {
                    $nilai_kinerja = 0;
                }
            }

            if ($nilai_kinerja > 100) {
                $nilai_kinerja = 100;
            }

            $value->nilai_kinerja = round($nilai_kinerja,2);

            // if ($aktivitas[0]->count !== null) {
            //     $value->aktivitas = $aktivitas;            
            // }else{
            //     $value->aktivitas = [];            
            // }
            
        }        

        if ($myArray) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $myArray
            ]);
        } else {
            return response()->json([
                'message' => 'Data belum ada',
                'status' => false,
                'data' => $myArray
            ]);
        }
        }
    }

    public function review_aktivitas_byPegawai($params){
        $bulan = request('bulan');
        $data = Aktivitas::with('skp')->where('id_pegawai',$params)->whereMonth('tanggal',$bulan)->get();
        return $data;
    }

}
