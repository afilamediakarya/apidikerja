<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\absen;
use App\Models\pegawai;
use DB;
use Auth;
class laporanRekapitulasiabsenController extends Controller
{

    public function cekHariLibur($params){
        // return $params;
        $temps = [];
        $libur = DB::table('tb_libur')->latest()->get();
        foreach ($libur as $key => $value) {
            for ( $i = strtotime($value->start_end); $i <= strtotime($value->end_date); $i += (60 * 60 * 24)) {
                $temps[] = date( 'Y-m-d', $i );  
            }
        }

        for ($i=0; $i < count($params); $i++) { 
           $temps[] = $params[$i];
        }


        return $temps;
    }

    public function rekapByUser($startDate, $endDate){
        $startTime = strtotime($startDate);
        $endTime = strtotime($endDate);
        $getDatatanggal= [];
        
        $jmlHariKerja = $this->jmlHariKerja($startDate, $endDate);
        $hariLibur = $this->cekHariLibur($jmlHariKerja['weekend']);
        $jml_kehadiran = [];
        $temps_absensi = [
            'kmk' => [
                'kmk_30' => [],
                'kmk_60' => [],
                'kmk_90' => [],
                'kmk_90_keatas' => [],
            ],
            'cpk' => [
                'cpk_30' => [],
                'cpk_60' => [],
                'cpk_90' => [],
                'cpk_90_keatas' => [],
            ],
            'alpa' => []
        ];
        // return $hariLibur;
        for ( $i = $startTime; $i <= $endTime; $i = $i + 86400 ) {
            // for ($y=0; $y < count($hariLibur); $y++) { 
            //     if ($hariLibur[$y] != date( 'Y-m-d', $i )) {
            //         $getDatatanggal[]['date'] = date( 'Y-m-d', $i );
            //     } 
            // }

            // return in_array(date( 'Y-m-d', $i), $hariLibur); 

            if (in_array(date( 'Y-m-d', $i), $hariLibur) != 1) {
                $getDatatanggal[]['date'] = date( 'Y-m-d', $i);   
            }  
        }

        // return $startDate. ' - '.$endDate;
        $result = [];
        $rekapAbsen = [];
        $pegawai = pegawai::select('nama','nip','id_satuan_kerja')->where('id',Auth::user()->id_pegawai)->first();
        // $getAbsen = DB::table('tb_absen')->where('id_pegawai',Auth::user()->id_pegawai)->get();
        // $getDatatanggal = DB::table('tb_absen')->select(DB::raw('tanggal_absen as date'))->where('tanggal_absen','>=',$startDate)->where('tanggal_absen','<=',$endDate)->where('id_pegawai',Auth::user()->id_pegawai)->groupBy('date')->orderBy('date')->get();


        foreach ($getDatatanggal as $key => $value) {
            $dataAbsen = [];
            $getAbsen = DB::table('tb_absen')->where('id_pegawai',Auth::user()->id_pegawai)->where('tanggal_absen',$value['date'])->get();
            foreach ($getAbsen as $i => $v) {
                // jml_kehadiran
                $keterangan = '';
                if ($v->jenis == 'checkin') {
                    $selisih_waktu = $this->konvertWaktu('checkin',$v->waktu_absen);
                    // return $selisih_waktu;
             
                    if ($selisih_waktu >= 1 && $selisih_waktu <= 30) {
                        $temps_absensi['kmk']['kmk_30'][] = $selisih_waktu;
                    }elseif($selisih_waktu >= 31 && $selisih_waktu <= 60){
                        $temps_absensi['kmk']['kmk_60'][] = $selisih_waktu;
                    }elseif($selisih_waktu >= 61 && $selisih_waktu <= 90){
                        $temps_absensi['kmk']['kmk_90'][] = $selisih_waktu;;
                    }elseif($selisih_waktu >= 91){
                        $temps_absensi['kmk']['kmk_90_keatas'][] = $selisih_waktu;
                    }
                    if ($selisih_waktu > 0 ) {
                        $keterangan = 'Telat '.$selisih_waktu. ' menit';
                    }else{
                        $keterangan = 'Tepat waktu';
                    }
                }else{
                    $jml_kehadiran[] = $v->jenis;
                    $selisih_waktu = $this->konvertWaktu('checkout',$v->waktu_absen);
          
                    if ($selisih_waktu >= 1 && $selisih_waktu <= 30) {
                        $temps_absensi['cpk']['cpk_30'][] = $selisih_waktu;
                    }elseif($selisih_waktu >= 31 && $selisih_waktu <= 60){
                        $temps_absensi['cpk']['cpk_60'][] = $selisih_waktu;
                    }elseif($selisih_waktu >= 61 && $selisih_waktu <= 90){
                        $temps_absensi['cpk']['cpk_90'][] = $selisih_waktu;;
                    }elseif($selisih_waktu >= 91){
                        $temps_absensi['cpk']['cpk_90_keatas'][] = $selisih_waktu;
                    }

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

                if ($value['date'] > date('Y-m-d')) {
                    $rekapAbsen[$key] = [
                        'tanggal' =>$value['date'],
                        'data_tanggal'=>[] 
                    ];
                }else{
                   if ($dataAbsen !== []) {
                        $rekapAbsen[$key] = [
                            'tanggal' =>$value['date'],
                            'data_tanggal'=>$dataAbsen 
                        ];
                   }else{
                        $temps_absensi['alpa'][] = $key;
                        $rekapAbsen[$key] = [
                            'tanggal' =>$value['date'],
                            'data_tanggal'=>[
                                'jenis' => '',
                                'status_absen' => 'tanpa keterangan',
                                'waktu_absen' => '00:00:00',
                                'keterangan' => 'tanpa keterangan'
                            ] 
                        ];
                   }
                }

         
        }

        $jml_potongan_kehadiran = (count($temps_absensi['alpa']) * 3) + (count($temps_absensi['kmk']['kmk_30'])) + (count($temps_absensi['kmk']['kmk_60'])) + (count($temps_absensi['kmk']['kmk_90'])) + (count($temps_absensi['kmk']['kmk_90_keatas'])) + (count($temps_absensi['cpk']['cpk_30'])) + (count($temps_absensi['cpk']['cpk_60'])) + (count($temps_absensi['cpk']['cpk_90'])) + count($temps_absensi['cpk']['cpk_90_keatas']) * 1.5;

        $persentase_pemotongan_tunjangan = ($jml_potongan_kehadiran / 100) * 0.4;

        $result['jml_hari_kerja'] = count($rekapAbsen);
        $result['kehadiran'] = count($jml_kehadiran);
        $result['potongan_kehadiran'] = $jml_potongan_kehadiran;
        $result['persentase_pemotongan'] = round($persentase_pemotongan_tunjangan,2);
        $result['pegawai'] = $pegawai;
        $result['tanpa_keterangan'] = count($temps_absensi['alpa']);
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

    public function viewrekapByUser($startDate, $endDate, $id_pegawai){
        $startTime = strtotime($startDate);
        $endTime = strtotime($endDate);
        $getDatatanggal= [];
        
        $jmlHariKerja = $this->jmlHariKerja($startDate, $endDate);
        $hariLibur = $this->cekHariLibur($jmlHariKerja['weekend']);
        $jml_kehadiran = [];
        $temps_absensi = [
            'kmk' => [
                'kmk_30' => [],
                'kmk_60' => [],
                'kmk_90' => [],
                'kmk_90_keatas' => [],
            ],
            'cpk' => [
                'cpk_30' => [],
                'cpk_60' => [],
                'cpk_90' => [],
                'cpk_90_keatas' => [],
            ],
            'alpa' => []
        ];
        for ( $i = $startTime; $i <= $endTime; $i = $i + 86400 ) {
            if (in_array(date( 'Y-m-d', $i), $hariLibur) != 1) {
                $getDatatanggal[]['date'] = date( 'Y-m-d', $i);   
            }  
        }

        $result = [];
        $rekapAbsen = [];
        $pegawai = pegawai::select('nama','nip','id_satuan_kerja')->where('id',$id_pegawai)->first();

        foreach ($getDatatanggal as $key => $value) {
            $dataAbsen = [];
            $getAbsen = DB::table('tb_absen')->where('id_pegawai',$id_pegawai)->where('tanggal_absen',$value['date'])->get();
            foreach ($getAbsen as $i => $v) {
           
                $keterangan = '';
                if ($v->jenis == 'checkin') {
                    $selisih_waktu = $this->konvertWaktu('checkin',$v->waktu_absen);
             
                    if ($selisih_waktu >= 1 && $selisih_waktu <= 30) {
                        $temps_absensi['kmk']['kmk_30'][] = $selisih_waktu;
                    }elseif($selisih_waktu >= 31 && $selisih_waktu <= 60){
                        $temps_absensi['kmk']['kmk_60'][] = $selisih_waktu;
                    }elseif($selisih_waktu >= 61 && $selisih_waktu <= 90){
                        $temps_absensi['kmk']['kmk_90'][] = $selisih_waktu;;
                    }elseif($selisih_waktu >= 91){
                        $temps_absensi['kmk']['kmk_90_keatas'][] = $selisih_waktu;
                    }
                    if ($selisih_waktu > 0 ) {
                        $keterangan = 'Telat '.$selisih_waktu.' menit';
                    }else{
                        $keterangan = 'Tepat waktu';
                    }
                }else{
                    $jml_kehadiran[] = $v->jenis;
                    $selisih_waktu = $this->konvertWaktu('checkout',$v->waktu_absen);
          
                    if ($selisih_waktu >= 1 && $selisih_waktu <= 30) {
                        $temps_absensi['cpk']['cpk_30'][] = $selisih_waktu;
                    }elseif($selisih_waktu >= 31 && $selisih_waktu <= 60){
                        $temps_absensi['cpk']['cpk_60'][] = $selisih_waktu;
                    }elseif($selisih_waktu >= 61 && $selisih_waktu <= 90){
                        $temps_absensi['cpk']['cpk_90'][] = $selisih_waktu;;
                    }elseif($selisih_waktu >= 91){
                        $temps_absensi['cpk']['cpk_90_keatas'][] = $selisih_waktu;
                    }

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

                if ($value['date'] > date('Y-m-d')) {
                    $rekapAbsen[$key] = [
                        'tanggal' =>$value['date'],
                        'data_tanggal'=>[] 
                    ];
                }else{
                   if ($dataAbsen !== []) {
                        $rekapAbsen[$key] = [
                            'tanggal' =>$value['date'],
                            'data_tanggal'=>$dataAbsen 
                        ];
                   }else{
                        $temps_absensi['alpa'][] = $key;
                        $rekapAbsen[$key] = [
                            'tanggal' =>$value['date'],
                            'data_tanggal'=>[
                                'jenis' => '',
                                'status_absen' => 'tanpa keterangan',
                                'waktu_absen' => '00:00:00',
                                'keterangan' => 'tanpa keterangan'
                            ] 
                        ];
                   }
                }

         
        }

        $jml_potongan_kehadiran = (count($temps_absensi['alpa']) * 3) + (count($temps_absensi['kmk']['kmk_30'])) + (count($temps_absensi['kmk']['kmk_60'])) + (count($temps_absensi['kmk']['kmk_90'])) + (count($temps_absensi['kmk']['kmk_90_keatas'])) + (count($temps_absensi['cpk']['cpk_30'])) + (count($temps_absensi['cpk']['cpk_60'])) + (count($temps_absensi['cpk']['cpk_90'])) + count($temps_absensi['cpk']['cpk_90_keatas']) * 1.5;

        $persentase_pemotongan_tunjangan = ($jml_potongan_kehadiran / 100) * 0.4;

        $result['jml_hari_kerja'] = count($rekapAbsen);
        $result['kehadiran'] = count($jml_kehadiran);
        $result['potongan_kehadiran'] = $jml_potongan_kehadiran;
        $result['persentase_pemotongan'] = round($persentase_pemotongan_tunjangan,2);
        $result['pegawai'] = $pegawai;
        $result['tanpa_keterangan'] = count($temps_absensi['alpa']);
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
        $selisih_waktu = '';
        if ($params == 'checkin') {
            $waktu_tetap_absen = strtotime('08:00:00');
            $waktu_absen = strtotime($waktu); 
            $diff = $waktu_absen - $waktu_tetap_absen;
        }else{
            $waktu_tetap_absen = strtotime('16:00:00');
            $waktu_absen = strtotime($waktu); 
            $diff = $waktu_tetap_absen - $waktu_absen;
            // return $diff;
        }

        if ($diff > 0) {
            // $jam = floor($diff/3600);
            // $selisih_waktu = $diff%3600;
            $menit = floor($diff/60);
        }else{
            $diff = 0;
        }

        

        return $menit;
    }

    public function jmlHariKerja($startDate, $endDate){
        $tanggal_awal = strtotime($startDate);
        $tanggal_akhir = strtotime($endDate);
 

        $harikerja = array();
        for ($i=$tanggal_awal; $i <= $tanggal_akhir; $i += (60 * 60 * 24)) {
            if (date('w', $i) !== '0' && date('w', $i) !== '6') {
                $harikerja['hari_kerja'][] = date('Y-m-d', $i);
            }else{
                $harikerja['weekend'][] = date('Y-m-d', $i);
            }
        }

        // $jumlah_hari = count($harikerja);

        return $harikerja;

    }

    public function rekapByAdminOpd($startDate, $endDate,$satuan_kerja){
        
        $satker = intval($satuan_kerja);
        $result = [];
        $pegawai_data = [];
        $satuan_kerja_ = '';
        $pegawaiBySatuanKerja = '';
        $getDatatanggal= [];
        $startTime = strtotime($startDate);
        $endTime = strtotime($endDate);
        $jmlHariKerja = $this->jmlHariKerja($startDate, $endDate);
        $hariLibur = $this->cekHariLibur($jmlHariKerja['weekend']);

         for ( $i = $startTime; $i <= $endTime; $i = $i + 86400 ) {

            if (in_array(date( 'Y-m-d', $i), $hariLibur) != 1) {
                $getDatatanggal[]['date'] = date( 'Y-m-d', $i);   
            }  
        }


        // return count($jmlHariKerja['hari_kerja']) - count($getDatatanggal);


        if ($satker > 0) {
            $getSatuankerjaById = DB::table('tb_satuan_kerja')->where('id',$satker)->first();
            $satuan_kerja_ = $getSatuankerjaById->nama_satuan_kerja;
            $pegawaiBySatuanKerja = DB::table('tb_pegawai')->select('id','nama')->where('id_satuan_kerja',$satuan_kerja)->get();
        }else{   
            $pegawai = pegawai::where('id',Auth::user()->id_pegawai)->first();
            $satuan_kerja_ = $pegawai['satuan_kerja']['nama_satuan_kerja'];
            $pegawaiBySatuanKerja = DB::table('tb_pegawai')->select('id','nama')->where('id_satuan_kerja',$pegawai->id_satuan_kerja)->get();
        }

        foreach ($pegawaiBySatuanKerja as $key => $value) {
            $getAbsenPegawai = absen::where('id_pegawai',$value->id)->select('id','id_pegawai','waktu_absen','status','jenis')->where('tanggal_absen','>=',$startDate)->where('tanggal_absen','<=',$endDate)->groupBy('tb_absen.id')->get();

               if (count($getAbsenPegawai) > 0) {
                   $pegawai_data[] = $getAbsenPegawai;
               }else{
                   $pegawai = pegawai::select('nama','nip')->where('id',$value->id)->first();
                   $pegawai_data[][0] =[
                        'pegawai' =>$pegawai
                   ];
               }
        }

        // return $pegawai_data;

        // $jml_hari = $this->jmlHariKerja($startDate, $endDate);

        return $result = [
            'satuan_kerja' => $satuan_kerja_,
            'hari_kerja' => count($getDatatanggal),
            'pegawai' => $pegawai_data
        ];
        
    }
}
