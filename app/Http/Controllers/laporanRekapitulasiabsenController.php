<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\absen;
use App\Models\pegawai;
use DB;
use Auth;
class laporanRekapitulasiabsenController extends Controller
{
    public function rekapByUser(){
        $result = [];
    //    return Auth::user()->id_pegawai;
        $rekapAbsen = [];
        $pegawai = pegawai::where('id',Auth::user()->id_pegawai)->first();
        // $getAbsen = DB::table('tb_absen')->where('id_pegawai',Auth::user()->id_pegawai)->get();
        $getDatatanggal = DB::table('tb_absen')->select(DB::raw('tanggal_absen as date'))->where('id_pegawai',Auth::user()->id_pegawai)->groupBy('date')->orderBy('date')->get();
        // return $getDatatanggal;
        foreach ($getDatatanggal as $key => $value) {
            $dataAbsen = [];
            $getAbsen = DB::table('tb_absen')->where('id_pegawai',Auth::user()->id_pegawai)->where('tanggal_absen',$value->date)->get();
            // return $getAbsen;
            foreach ($getAbsen as $i => $v) {
                // return $v;
                $keterangan = '';
                if ($v->jenis == 'checkin') {
                    $selisih_waktu = $this->konvertWaktu('checkin',$v->waktu_absen);
                    if ($selisih_waktu > 0 ) {
                        $keterangan = 'Telat '.$selisih_waktu.' menit';
                    }else{
                        $keterangan = 'Tepat waktu';
                    }
                }else{
                    // return $v->waktu_absen;
                    $selisih_waktu = $this->konvertWaktu('checkout',$v->waktu_absen);
                    if ($selisih_waktu > 0 ) {
                        $keterangan = 'Cepat '.$selisih_waktu.' menit';
                    }else{
                        $keterangan = 'Tepat waktu';
                    }
                }
                
                $dataAbsen[$i] = [
                    'jenis' => $v->jenis,
                    'status_absen' => $v->status,
                    'waktu_absen' => $v->waktu_absen,
                    'keterangan' => $keterangan
                ];
            }
            $rekapAbsen[$key] = [
                'tanggal' =>$value->date,
                'data_tanggal'=>$dataAbsen 
            ];
        }
        $result['pegawai'] = $pegawai['nama'];
        $result['data_absen'] = $rekapAbsen;

        if ($result) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $result
            ]);
        }else{
            return response()->json([
                'message' => 'Failed',
                'status' => false
            ]);
        }
    }

    public function konvertWaktu($params,$waktu){
        $diff = '';
        if ($params == 'checkin') {
            $waktu_tetap_absen = strtotime('08:00:00');
            $waktu_absen = strtotime($waktu); 
            $diff = $waktu_absen - $waktu_tetap_absen;
        }else{
            $waktu_tetap_absen = strtotime('17:00:00');
            $waktu_absen = strtotime($waktu); 
            $diff = $waktu_tetap_absen - $waktu_absen;
        }

        $jam = floor($diff/(60*60));
        $menit = $diff - $jam * (60*60);
        $selisih_waktu = floor($menit/60);

        return $selisih_waktu;
    }
}
