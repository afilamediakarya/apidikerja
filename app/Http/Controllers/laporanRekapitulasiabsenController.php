<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\absen;
use App\Models\pegawai;
use DB;
use Auth;
class laporanRekapitulasiabsenController extends Controller
{
    public function rekapByUser($startDate, $endDate){
        // return $startDate. ' - '.$endDate;
        $result = [];
        $rekapAbsen = [];
        $pegawai = pegawai::where('id',Auth::user()->id_pegawai)->first();
        // $getAbsen = DB::table('tb_absen')->where('id_pegawai',Auth::user()->id_pegawai)->get();
        $getDatatanggal = DB::table('tb_absen')->select(DB::raw('tanggal_absen as date'))->where('tanggal_absen','>=',$startDate)->where('tanggal_absen','<=',$endDate)->where('id_pegawai',Auth::user()->id_pegawai)->groupBy('date')->orderBy('date')->get();
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
        $result['pegawai'] = $pegawai;
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

    public function jmlHariKerja($startDate, $endDate){
        $tanggal_awal = strtotime($startDate);
        $tanggal_akhir = strtotime($endDate);

        $harikerja = array();
        for ($i=$tanggal_awal; $i <= $tanggal_akhir; $i += (60 * 60 * 24)) {
            if (date('w', $i) !== '0' && date('w', $i) !== '6') {
                $harikerja[] = $i;
            }
        }

        $jumlah_hari = count($harikerja);

        return $jumlah_hari;

    }

    public function rekapByAdminOpd($startDate, $endDate){
        $result = [];
        $pegawai_data = [];
        $pegawai = DB::table('tb_pegawai')->select('id_satuan_kerja')->where('id',Auth::user()->id_pegawai)->first();
        $pegawaiBySatuanKerja = DB::table('tb_pegawai')->select('id','nama')->where('id_satuan_kerja',$pegawai->id_satuan_kerja)->get();

        foreach ($pegawaiBySatuanKerja as $key => $value) {
            // $getAbsenPegawai = absen::where('id_pegawai',$value->id)->select('id_pegawai',DB::raw("SUM(tb_absen.status = 'alpa') as tanpa_keterangan"), DB::raw("SUM(tb_absen.status = 'hadir') as hadir"),DB::raw("SUM(tb_absen.status = 'alpa') as potongan"))->where('tanggal_absen','>=',$startDate)->where('tanggal_absen','<=',$endDate)->groupBy('tb_absen.id_pegawai')->get();
            $getAbsenPegawai = absen::where('id_pegawai',$value->id)->select('id','id_pegawai','waktu_absen','status','jenis',DB::raw("SUM(tb_absen.status = 'alpa') as tanpa_keterangan"), DB::raw("SUM(tb_absen.status = 'hadir') as hadir"))->where('tanggal_absen','>=',$startDate)->where('tanggal_absen','<=',$endDate)->groupBy('tb_absen.id')->get();

            // foreach ($getAbsenPegawai as $key => $value) {
                
            // }
           if (!empty($getAbsenPegawai)) {
               $pegawai_data[$key] = $getAbsenPegawai;
           }
        }

        // return $pegawai_data;

        $jml_hari = $this->jmlHariKerja($startDate, $endDate);
        

        return $result = [
            'hari_kerja' => $jml_hari,
            'pegawai' => $pegawai_data
        ];

        // return $pegawaiBySatuanKerja;

        
    }
}
