<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\absen;
use App\Models\pegawai;
use DB;
use Auth;
use Carbon\Carbon;

class laporanRekapitulasiabsenController extends Controller
{

    public function cekHariLibur($params)
    {
        // return $params;
        $temps = [];
        $libur = DB::table('tb_libur')->latest()->get();
        foreach ($libur as $key => $value) {
            for ($i = strtotime($value->start_end); $i <= strtotime($value->end_date); $i += (60 * 60 * 24)) {
                $temps[] = date('Y-m-d', $i);
            }
        }

        if (isset($params['weekend'])) {
            for ($i = 0; $i < count($params['weekend']); $i++) {
                $temps[] = $params['weekend'][$i];
            }
        }

        return $temps;
    }

    public function rekapByUser($startDate, $endDate, $pegawai = null)
    {
        $startTime = strtotime($startDate);
        $endTime = strtotime($endDate);
        $getDatatanggal = [];

        $jmlHariKerja = $this->jmlHariKerja($startDate, $endDate);
        $hariLibur = $this->cekHariLibur($jmlHariKerja);
        $jml_kehadiran = [];
        $endDate_if = '';
        $monday = array();
        $pegawai_ = '';
        $pegawai == null ? $pegawai_ = request('pegawai') : $pegawai_ = $pegawai;
       
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
            ]
        ];
        $jumlah_alpa = 0;
        $potongan_masuk_kerja = 0;
        $potongan_pulang_kerja = 0;
        $jumlah_tidak_apel = 0;

        $res_jml_alpa = 0;
        $res_jml_tidak_apel = 0;

        $endDate > date('Y-m-d') ? $endDate_if = strtotime(date('Y-m-d')) : $endDate_if = $endTime;

        $jumlah_ikut_apel = 0;
       
        for ($i = $startTime; $i <= $endTime; $i = $i + 86400) {
            if (in_array(date('Y-m-d', $i), $hariLibur) !== 1) {
                $getDatatanggal[]['date'] = date('Y-m-d', $i);
            }
        }

         for ($i = $startTime; $i <= $endDate_if; $i = $i + 86400) {
            if (in_array(date('Y-m-d', $i), $hariLibur) !== 1) {
                $date_ = date('Y-m-d', $i);
                $day_ = date('l', strtotime($date_));
                if ($day_ == 'Monday') {
                   array_push($monday,date('Y-m-d',strtotime($date_)));
                }      
            }

        }

        $result = [];
        $rekapAbsen = [];
        $tes = [];
        // return $pegawai;
        $pegawai = pegawai::select('nama', 'nip', 'id_satuan_kerja')->where('id', $pegawai_)->first();
    
        // return  $getAbsen = DB::table('tb_absen')->where('id_pegawai', $pegawai_)->where('validation', 1)->where('tanggal_absen','2023-03-06')->groupBy('tanggal_absen','jenis')->get();
        foreach ($getDatatanggal as $key => $value) {
            $dataAbsen = [];
            $getAbsen = DB::table('tb_absen')->where('id_pegawai', $pegawai_)->where('validation', 1)->where('tanggal_absen', $value['date'])->groupBy('tanggal_absen','jenis')->get();

            
            // if ($value['date'] === '2023-04-02') {
            //     return $getAbsen;
            // }

            foreach ($getAbsen as $i => $v) {
                $keterangan = '';
                
                if ($v->jenis == 'checkin') {
                    $jml_kehadiran[$v->tanggal_absen] = $v->jenis;
                    
                    if (in_array($v->tanggal_absen, $monday)){
                        
                        if ($v->status !== 'apel' && $v->status !== 'dinas luar' && $v->status !== 'cuti' && $v->status !== 'izin') {
                            $jumlah_tidak_apel += 1;
                        }
                    }

                 $selisih_waktu = $this->konvertWaktu('checkin', $v->waktu_absen);
                    if ($selisih_waktu >= 1 && $selisih_waktu <= 30) {
                        $temps_absensi['kmk']['kmk_30'][] = $selisih_waktu;
                    } elseif ($selisih_waktu >= 31 && $selisih_waktu <= 60) {
                        $temps_absensi['kmk']['kmk_60'][] = $selisih_waktu;
                    } elseif ($selisih_waktu >= 61 && $selisih_waktu <= 90) {
                        $temps_absensi['kmk']['kmk_90'][] = $selisih_waktu;;
                    } elseif ($selisih_waktu >= 91) {
                        $temps_absensi['kmk']['kmk_90_keatas'][] = $selisih_waktu;
                    }
                    if ($selisih_waktu > 0) {
                        $keterangan = 'Telat ' . $selisih_waktu . ' menit';
                    } else {
                        $keterangan = 'Tepat waktu';
                    }
                } else {

                    $selisih_waktu = $this->konvertWaktu('checkout', $v->waktu_absen);
                    
                    if ($selisih_waktu >= 1 && $selisih_waktu <= 30) {
                        $temps_absensi['cpk']['cpk_30'][] = $selisih_waktu;
                    } elseif ($selisih_waktu >= 31 && $selisih_waktu <= 60) {
                        $temps_absensi['cpk']['cpk_60'][] = $selisih_waktu;
                    } elseif ($selisih_waktu >= 61 && $selisih_waktu <= 90) {
                        $temps_absensi['cpk']['cpk_90'][] = $selisih_waktu;;
                    } elseif ($selisih_waktu >= 91) {
                        $temps_absensi['cpk']['cpk_90_keatas'][] = $selisih_waktu;
                    }

                    if ($selisih_waktu > 0) {
                        $keterangan = 'Cepat ' . $selisih_waktu . ' menit';
                    } else {
                        $keterangan = 'Tepat waktu';
                    }
                }

                
                $dataAbsen[$i] = [
                    'jenis' => $v->jenis,
                    'status_absen' => $v->status,
                    'tanggal_absen' => $v->tanggal_absen,
                    'waktu_absen' => $v->waktu_absen,
                    'keterangan' => $keterangan
                ];
            }

            

            // return $dataAbsen;
            if (count($dataAbsen) == 1) {
                if ($value['date'] < date('Y-m-d')) {

                    $dataAbsen[1] = [
                        'jenis' => 'checkout',
                        'status_absen' => 'hadir',
                        'waktu_absen' => '14:00:00',
                        'keterangan' => 'cepat 90 menit'
                    ];
                    $jml_kehadiran[$dataAbsen[0]['tanggal_absen']] = 'checkout';
                }

                $temps_absensi['cpk']['cpk_90_keatas'][] = 90; 
            }
          
            
            if ($value['date'] > date('Y-m-d')) {
                // return 'a';
                $rekapAbsen[$key] = [
                    'tanggal' => $value['date'],
                    'data_tanggal' => []
                ];
            } else {
            
                if ($dataAbsen !== []) {
                    $rekapAbsen[$key] = [
                        'tanggal' => $value['date'],
                        'data_tanggal' => $dataAbsen
                    ];
                } else {
                    $jumlah_alpa += 1;
                    $rekapAbsen[$key] = [
                        'tanggal' => $value['date'],
                        'data_tanggal' => [
                            'jenis' => '',
                            'status_absen' => 'tanpa keterangan',
                            'waktu_absen' => '00:00:00',
                            'keterangan' => 'tanpa keterangan'
                        ]
                    ];

                    if (in_array($value['date'], $monday)){
                        $jumlah_tidak_apel += 1;
                    }

                }
            }
        }

        // return $tes;
        // return $rekapAbsen;

         $jml_potongan_kehadiran = ($jumlah_alpa * 3) + (count($temps_absensi['kmk']['kmk_30']) * 0.5) + (count($temps_absensi['kmk']['kmk_60'])) + (count($temps_absensi['kmk']['kmk_90']) * 1.25) + (count($temps_absensi['kmk']['kmk_90_keatas']) * 1.5) + (count($temps_absensi['cpk']['cpk_30']) * 0.5) + (count($temps_absensi['cpk']['cpk_60'])) + (count($temps_absensi['cpk']['cpk_90']) * 1.25) + (count($temps_absensi['cpk']['cpk_90_keatas']) * 1.5);

         $res_jml_alpa = $jumlah_alpa * 3;
         $res_jml_tidak_apel = $jumlah_tidak_apel * 2;

        $potongan_masuk_kerja = (count($temps_absensi['kmk']['kmk_30']) * 0.5) + (count($temps_absensi['kmk']['kmk_60']) * 1) + (count($temps_absensi['kmk']['kmk_90']) * 1.25) + (count($temps_absensi['kmk']['kmk_90_keatas']) * 1.5); 
        $potongan_pulang_kerja = (count($temps_absensi['cpk']['cpk_30']) * 0.5) + (count($temps_absensi['cpk']['cpk_60']) * 1) + (count($temps_absensi['cpk']['cpk_90']) * 1.25) + (count($temps_absensi['cpk']['cpk_90_keatas']) * 1.5); 



        $persentase_pemotongan_tunjangan = $jml_potongan_kehadiran * 0.4;

        $jml_potongan_kehadiran_kerja = $res_jml_alpa + $potongan_masuk_kerja + $potongan_pulang_kerja + $res_jml_tidak_apel;

        $result['jml_hari_kerja'] = count($rekapAbsen);
        $result['kehadiran'] = count($jml_kehadiran);
        // $result['persentase_pemotongan'] = round($persentase_pemotongan_tunjangan, 2);
        $result['tanpa_keterangan'] = $jumlah_alpa;
        $result['pegawai'] = $pegawai;
        $result['data_absen'] = $rekapAbsen;

        $result['potongan_tanpa_keterangan'] = $res_jml_alpa;
        $result['potongan_masuk_kerja'] = $potongan_masuk_kerja;
        $result['potongan_pulang_kerja'] = $potongan_pulang_kerja;
        $result['potongan_apel'] = $res_jml_tidak_apel;
        $result['jumlah_tidak_apel'] = $jumlah_tidak_apel;
        $result['jml_potongan_kehadiran_kerja'] = $jml_potongan_kehadiran_kerja;

        if ($result) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $result
            ]);
        } else {
            return response()->json([
                'message' => 'Failed',
                'status' => false
            ]);
        }
    }

    public function viewrekapByUser($startDate, $endDate, $id_pegawai)
    {

        $startTime = strtotime($startDate);
        $endTime = strtotime($endDate);
        $getDatatanggal = [];

        $jmlHariKerja = $this->jmlHariKerja($startDate, $endDate);
        $hariLibur = $this->cekHariLibur($jmlHariKerja);
        $jml_kehadiran = [];
        $endDate_if = '';
        $monday = array();
        $pegawai_ = $id_pegawai;
       
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
            ]
        ];
        $jumlah_alpa = 0;
        $potongan_masuk_kerja = 0;
        $potongan_pulang_kerja = 0;
        $jumlah_tidak_apel = 0;

        $res_jml_alpa = 0;
        $res_jml_tidak_apel = 0;

        $endDate > date('Y-m-d') ? $endDate_if = strtotime(date('Y-m-d')) : $endDate_if = $endTime;


        // return $hariLibur;
        for ($i = $startTime; $i <= $endTime; $i = $i + 86400) {
            if (in_array(date('Y-m-d', $i), $hariLibur) != 1) {
                $getDatatanggal[]['date'] = date('Y-m-d', $i);
            }
        }

         for ($i = $startTime; $i <= $endDate_if; $i = $i + 86400) {
            if (in_array(date('Y-m-d', $i), $hariLibur) != 1) {
                $date_ = date('Y-m-d', $i);
                $day_ = date('l', strtotime($date_));
                if ($day_ == 'Monday') {
                   array_push($monday,date('Y-m-d',strtotime($date_)));
                }      
            }

        }

        $result = [];
        $rekapAbsen = [];
        $tes = [];
        // return $pegawai;
        $pegawai = pegawai::select('nama', 'nip', 'id_satuan_kerja')->where('id', $pegawai_)->first();
        foreach ($getDatatanggal as $key => $value) {
            $dataAbsen = [];
            $getAbsen = DB::table('tb_absen')->where('id_pegawai', $pegawai_)->where('validation', 1)->where('tanggal_absen', $value['date'])->groupBy('tanggal_absen','jenis')->get();
            foreach ($getAbsen as $i => $v) {
                $keterangan = '';
                if ($v->jenis == 'checkin') {
                    $jml_kehadiran[$v->tanggal_absen] = $v->jenis;
                     if (in_array($v->tanggal_absen, $monday)){
                        if ($v->status !== 'apel' && $v->status !== 'dinas luar' && $v->status !== 'cuti' && $v->status !== 'izin') {
                            $jumlah_tidak_apel += 1;
                        }
                    }

                 $selisih_waktu = $this->konvertWaktu('checkin', $v->waktu_absen);
                    if ($selisih_waktu >= 1 && $selisih_waktu <= 30) {
                        $temps_absensi['kmk']['kmk_30'][] = $selisih_waktu;
                    } elseif ($selisih_waktu >= 31 && $selisih_waktu <= 60) {
                        $temps_absensi['kmk']['kmk_60'][] = $selisih_waktu;
                    } elseif ($selisih_waktu >= 61 && $selisih_waktu <= 90) {
                        $temps_absensi['kmk']['kmk_90'][] = $selisih_waktu;;
                    } elseif ($selisih_waktu >= 91) {
                        $temps_absensi['kmk']['kmk_90_keatas'][] = $selisih_waktu;
                    }
                    if ($selisih_waktu > 0) {
                        $keterangan = 'Telat ' . $selisih_waktu . ' menit';
                    } else {
                        $keterangan = 'Tepat waktu';
                    }
                } else {

                    $selisih_waktu = $this->konvertWaktu('checkout', $v->waktu_absen);
                    if ($selisih_waktu >= 1 && $selisih_waktu <= 30) {
                        $temps_absensi['cpk']['cpk_30'][] = $selisih_waktu;
                    } elseif ($selisih_waktu >= 31 && $selisih_waktu <= 60) {
                        $temps_absensi['cpk']['cpk_60'][] = $selisih_waktu;
                    } elseif ($selisih_waktu >= 61 && $selisih_waktu <= 90) {
                        $temps_absensi['cpk']['cpk_90'][] = $selisih_waktu;;
                    } elseif ($selisih_waktu >= 91) {
                        $temps_absensi['cpk']['cpk_90_keatas'][] = $selisih_waktu;
                    }

                    if ($selisih_waktu > 0) {
                        $keterangan = 'Cepat ' . $selisih_waktu . ' menit';
                    } else {
                        $keterangan = 'Tepat waktu';
                    }
                }

                $dataAbsen[$i] = [
                    'jenis' => $v->jenis,
                    'status_absen' => $v->status,
                    'tanggal_absen' => $v->tanggal_absen,
                    'waktu_absen' => $v->waktu_absen,
                    'keterangan' => $keterangan
                ];
            }

            if (count($dataAbsen) == 1) {
                if ($value['date'] < date('Y-m-d')) {

                    $dataAbsen[1] = [
                        'jenis' => 'checkout',
                        'status_absen' => 'hadir',
                        'waktu_absen' => '14:00:00',
                        'keterangan' => 'cepat 90 menit'
                    ];
                    $jml_kehadiran[$dataAbsen[0]['tanggal_absen']] = 'checkout';
                }

                $temps_absensi['cpk']['cpk_90_keatas'][] = 90; 
            }
          
            if ($value['date'] > date('Y-m-d')) {
                $rekapAbsen[$key] = [
                    'tanggal' => $value['date'],
                    'data_tanggal' => []
                ];
            } else {
                if ($dataAbsen !== []) {
                    $rekapAbsen[$key] = [
                        'tanggal' => $value['date'],
                        'data_tanggal' => $dataAbsen
                    ];
                } else {
                    $jumlah_alpa += 1;
                    // $tes[] = $value['date'];
                    $rekapAbsen[$key] = [
                        'tanggal' => $value['date'],
                        'data_tanggal' => [
                            'jenis' => '',
                            'status_absen' => 'tanpa keterangan',
                            'waktu_absen' => '00:00:00',
                            'keterangan' => 'tanpa keterangan'
                        ]
                    ];
                    if (in_array($value['date'], $monday)){
                        $jumlah_tidak_apel += 1;
                    }
                }
            }
        }

         $jml_potongan_kehadiran = ($jumlah_alpa * 3) + (count($temps_absensi['kmk']['kmk_30']) * 0.5) + (count($temps_absensi['kmk']['kmk_60'])) + (count($temps_absensi['kmk']['kmk_90']) * 1.25) + (count($temps_absensi['kmk']['kmk_90_keatas']) * 1.5) + (count($temps_absensi['cpk']['cpk_30']) * 0.5) + (count($temps_absensi['cpk']['cpk_60'])) + (count($temps_absensi['cpk']['cpk_90']) * 1.25) + (count($temps_absensi['cpk']['cpk_90_keatas']) * 1.5);

         $res_jml_alpa = $jumlah_alpa * 3;
         $res_jml_tidak_apel = $jumlah_tidak_apel * 2;

        $potongan_masuk_kerja = (count($temps_absensi['kmk']['kmk_30']) * 0.5) + (count($temps_absensi['kmk']['kmk_60']) * 1) + (count($temps_absensi['kmk']['kmk_90']) * 1.25) + (count($temps_absensi['kmk']['kmk_90_keatas']) * 1.5); 
        $potongan_pulang_kerja = (count($temps_absensi['cpk']['cpk_30']) * 0.5) + (count($temps_absensi['cpk']['cpk_60']) * 1) + (count($temps_absensi['cpk']['cpk_90']) * 1.25) + (count($temps_absensi['cpk']['cpk_90_keatas']) * 1.5); 



        $persentase_pemotongan_tunjangan = $jml_potongan_kehadiran * 0.4;

        $jml_potongan_kehadiran_kerja = $res_jml_alpa + $potongan_masuk_kerja + $potongan_pulang_kerja + $res_jml_tidak_apel;

        $result['jml_hari_kerja'] = count($rekapAbsen);
        $result['kehadiran'] = count($jml_kehadiran);
        // $result['persentase_pemotongan'] = round($persentase_pemotongan_tunjangan, 2);
        $result['tanpa_keterangan'] = $jumlah_alpa;
        $result['pegawai'] = $pegawai;
        $result['data_absen'] = $rekapAbsen;

        $result['potongan_tanpa_keterangan'] = $res_jml_alpa;
        $result['potongan_masuk_kerja'] = $potongan_masuk_kerja;
        $result['potongan_pulang_kerja'] = $potongan_pulang_kerja;
        $result['potongan_apel'] = $res_jml_tidak_apel;
        $result['jumlah_tidak_apel'] = $jumlah_tidak_apel;
        $result['jml_potongan_kehadiran_kerja'] = $jml_potongan_kehadiran_kerja;

        if ($result) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $result
            ]);
        } else {
            return response()->json([
                'message' => 'Failed',
                'status' => false
            ]);
        }
    }

    function getDateRange()
    {
        $start_date = '2023-03-24';
        $end_date = '2023-04-23';

        $dates = [];
        for ($date = Carbon::parse($start_date); $date->lte(Carbon::parse($end_date)); $date->addDay()) {
            $dates[] = $date->format('Y-m-d');
        }

        return $dates;
    }

    public function konvertWaktu($params, $waktu)
    {
   
        $diff = '';
        $selisih_waktu = '';
        $menit = 0;
        if ($params == 'checkin') {
            $waktu_tetap_absen = strtotime('08:00:00');
            $waktu_absen = strtotime($waktu);
            $diff = $waktu_absen - $waktu_tetap_absen;
        } else {
            $waktu_checkout = '16:00:00';
            $arr = $this->getDateRange();
            $key = array_search($waktu, $arr);
            return $key;

            if ($key !== false) {
                $waktu_checkout = '15:00:00';
            }

            $waktu_tetap_absen = strtotime($waktu_checkout);
            $waktu_absen = strtotime($waktu);
            $diff = $waktu_tetap_absen - $waktu_absen;
            // return $diff;
        }

        if ($diff > 0) {
            // $jam = floor($diff/3600);
            // $selisih_waktu = $diff%3600;
            $menit = floor($diff / 60);
        } else {
            $diff = 0;
        }



        return $menit;
    }

    public function jmlHariKerja($startDate, $endDate)
    {
        $tanggal_awal = strtotime($startDate);
        $tanggal_akhir = strtotime($endDate);


        $harikerja = array();
        for ($i = $tanggal_awal; $i <= $tanggal_akhir; $i += (60 * 60 * 24)) {
            if (date('w', $i) !== '0' && date('w', $i) !== '6') {
                $harikerja['hari_kerja'][] = date('Y-m-d', $i);
            } else {
                $harikerja['weekend'][] = date('Y-m-d', $i);
            }
        }

        // $jumlah_hari = count($harikerja);

        return $harikerja;
    }

    public function rekapByAdminOpd($startDate, $endDate, $satuan_kerja)
    {

        $satker = intval($satuan_kerja);

        $endDate_if = '';
      
        $result = [];
        $pegawai_data = [];
        $satuan_kerja_ = '';
        $pegawaiBySatuanKerja = '';
        $getDatatanggal = [];
        $startTime = strtotime($startDate);
        $endTime = strtotime($endDate);
        $monday = array();
        $count_monday = 0;
        $tempsRange = [];
        $jmlHariKerja = $this->jmlHariKerja($startDate, $endDate);

        $endDate > date('Y-m-d') ? $endDate_if = strtotime(date('Y-m-d')) : $endDate_if = $endTime;

        $range = array();
        // return $jmlHariKerja;
        if ($endDate <= date('Y-m-d')) {
            // return 'A';
            $range = $this->jmlHariKerja($startDate, $endDate);
        } else {
            $range = $this->jmlHariKerja($startDate, date('Y-m-d'));
        }

        // return $range;
    
        $hariLibur = $this->cekHariLibur($jmlHariKerja);
    
        foreach ($hariLibur as $k => $liburDay) {
            if (isset($range['hari_kerja'])) {
                if (in_array($liburDay, $range['hari_kerja'])) {
                    $index = array_search($liburDay, $range['hari_kerja']);
                    \array_splice($range['hari_kerja'], $index, 1);
                }
                $tempsRange = $range['hari_kerja'];
            }
        }
         for ($i = $startTime; $i <= $endTime; $i = $i + 86400) {
            if (in_array(date('Y-m-d', $i), $hariLibur) !== 1) {
                $getDatatanggal[]['date'] = date('Y-m-d', $i);
            }
        }

        for ($i = $startTime; $i <= $endDate_if; $i = $i + 86400) {
            if (in_array(date('Y-m-d', $i), $hariLibur) != 1) {
                $date_ = date('Y-m-d', $i);
                $day_ = date('l', strtotime($date_));
                if ($day_ == 'Monday') {
                    // $count_monday += 1;  
                    $monday[] = $date_;        
                }      
            }
        }


        if ($satker > 0) {
            $pegawaiBySatuanKerja = DB::table('tb_pegawai')->select('tb_pegawai.id', 'tb_pegawai.nama')
                ->join('tb_jabatan', 'tb_pegawai.id', '=', 'tb_jabatan.id_pegawai')
                ->where('tb_pegawai.id_satuan_kerja', $satker)
                ->orderBy('tb_jabatan.kelas_jabatan', 'desc')
                ->orderBy('tb_pegawai.nama', 'asc')
                ->get();
        } else {
            $pegawai = pegawai::where('id', Auth::user()->id_pegawai)->first();
            $satuan_kerja_ = $pegawai['satuan_kerja']['nama_satuan_kerja'];
            $pegawaiBySatuanKerja = DB::table('tb_pegawai')->select('id', 'nama')
                ->join('tb_jabatan', 'tb_pegawai.id', '=', 'tb_jabatan.id_pegawai')
                ->where('id_satuan_kerja', $pegawai->id_satuan_kerja)
                ->orderBy('nama', 'asc')
                ->get();
        }

        foreach ($pegawaiBySatuanKerja as $key => $value) {
            // return $value;
            $getAbsenPegawai = absen::where('id_pegawai', $value->id)
                ->select('id', 'id_pegawai', 'waktu_absen', 'status', 'jenis','tanggal_absen',DB::raw('COUNT(IF(status = "apel", 1, NULL)) "jumlah_apel"'),DB::raw('COUNT(IF(status = "hadir", 1, NULL)) "jumlah_hadir"'),DB::raw('COUNT(IF(status = "sakit", 1, NULL)) "jumlah_sakit"'),DB::raw('COUNT(IF(status = "cuti", 1, NULL)) "jumlah_cuti"'),DB::raw('COUNT(IF(status = "dinas luar", 1, NULL)) "jumlah_dinas_luar"'))
                ->whereIn('tanggal_absen',$getDatatanggal)
                ->where('validation', 1)
                ->groupBy('tb_absen.tanggal_absen','tb_absen.jenis')
                ->get();

            if (count($getAbsenPegawai) > 0) {
                $pegawai_data[] = $getAbsenPegawai;
            } else {
                $pegawai = pegawai::select('nama', 'nip')->where('id', $value->id)->first();
                $pegawai_data[][0] = [
                    'pegawai' => $pegawai
                ];
            }
        }
   
        

        return $result = [
            'satuan_kerja' => $satuan_kerja_,
            'hari_kerja' => count($getDatatanggal),
            'pegawai' => $pegawai_data,
            'range' => $tempsRange,
            'monday' => $monday
        ];
    }
}
