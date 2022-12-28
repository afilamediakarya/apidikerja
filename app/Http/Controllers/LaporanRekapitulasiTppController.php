<?php

namespace App\Http\Controllers;

use App\Models\absen;
use Illuminate\Http\Request;
use App\Models\pegawai;
use App\Models\skp;
use App\Models\aktivitas;
use DB;
use Auth;

class laporanRekapitulasiTppController extends Controller
{

    // rekap tpp
    public function rekapTpp()
    {
        $satuanKerja = request('satuan_kerja');
        $bulan = request('bulan');

        $currentDate = date("Y-{$bulan}-d");
        $startDate =  date("Y-m-01", strtotime($currentDate));
        $endDate =  date('Y-m-t', strtotime($currentDate));

        $getDatatanggal = [];

        $startTime = strtotime($startDate);
        $endTime = strtotime($endDate);

        $jmlHariKerja = (new laporanRekapitulasiabsenController)->jmlHariKerja($startDate, $endDate);

        $range = [];
        if ($endDate <= date('Y-m-d')) {
            $range = (new laporanRekapitulasiabsenController)->jmlHariKerja($startDate, $endDate);
        } else {
            $range = (new laporanRekapitulasiabsenController)->jmlHariKerja($startDate, date('Y-m-d'));
        }
        $hariLibur = (new laporanRekapitulasiabsenController)->cekHariLibur($jmlHariKerja);

        for ($i = $startTime; $i <= $endTime; $i = $i + 86400) {
            if (in_array(date('Y-m-d', $i), $hariLibur) != 1) {
                $getDatatanggal[]['date'] = date('Y-m-d', $i);
            }
        }


        $result = [];

        $namaSatuanKerja =
            DB::table('tb_pegawai')->select('tb_satuan_kerja.nama_satuan_kerja',)
            ->join('tb_satuan_kerja', 'tb_pegawai.id_satuan_kerja', '=', 'tb_satuan_kerja.id')
            ->where('tb_pegawai.id_satuan_kerja', $satuanKerja)
            ->first();

        $kepalaBadan = DB::table('tb_pegawai')->select('tb_jabatan.nama_jabatan',)
            ->join('tb_jabatan', 'tb_pegawai.id', '=', 'tb_jabatan.id_pegawai')
            ->where('tb_pegawai.id_satuan_kerja', $satuanKerja)
            ->where('tb_jabatan.nama_jabatan', $satuanKerja)
            ->first();

        if ($satuanKerja > 0) {
            $pegawaiBySatuanKerja = DB::table('tb_pegawai')->select('tb_pegawai.id', 'tb_pegawai.nama', 'tb_pegawai.nip', 'tb_pegawai.golongan', 'tb_pegawai.jenis_jabatan', 'tb_jabatan.nama_jabatan', 'tb_jabatan.nilai_jabatan', 'tb_jabatan.id_jenis_jabatan', 'tb_jenis_jabatan.level','tb_jabatan.target_waktu','tb_jabatan.kelas_jabatan')
                ->join('tb_jabatan', 'tb_pegawai.id', '=', 'tb_jabatan.id_pegawai')
                ->join('tb_jenis_jabatan', 'tb_jabatan.id_jenis_jabatan', '=', 'tb_jenis_jabatan.id')
                ->where('tb_pegawai.id_satuan_kerja', $satuanKerja)
                // ->where('tb_pegawai.id', 256)
                ->orderBy('tb_jabatan.id_jenis_jabatan', 'asc')
                ->get();
        } else {
            $pegawai = pegawai::where('id', Auth::user()->id_pegawai)->first();

            $pegawaiBySatuanKerja = DB::table('tb_pegawai')->select('tb_pegawai.id', 'tb_pegawai.nama', 'tb_pegawai.nip', 'tb_pegawai.golongan', 'tb_pegawai.jenis_jabatan', 'tb_jabatan.nama_jabatan', 'tb_jabatan.nilai_jabatan', 'tb_jabatan.id_jenis_jabatan', 'tb_jenis_jabatan.level','tb_jabatan.target_waktu','tb_jabatan.kelas_jabatan')
                ->join('tb_jabatan', 'tb_pegawai.id', '=', 'tb_jabatan.id_pegawai')
                ->join('tb_jenis_jabatan', 'tb_jabatan.id_jenis_jabatan', '=', 'tb_jenis_jabatan.id')
                ->where('id_satuan_kerja', $pegawai->id_satuan_kerja)
                ->orderBy('nama', 'asc')
                ->get();
        }

        foreach ($pegawaiBySatuanKerja as $key => $value) {

            $result = [];
            $jabatanByPegawai = DB::table('tb_jabatan')->where('id_pegawai', $value->id)->first();

            // pegawai::query()
            //     ->select('tb_pegawai.id','tb_pegawai.nama','tb_pegawai.nip','tb_pegawai.golongan','tb_jabatan.nama_jabatan','tb_jabatan.target_waktu','tb_jabatan.kelas_jabatan')
            //     ->with(['aktivitas'=> function($query) use ($bulan) {
            //         $query->select('id','id_pegawai','hasil',DB::raw("SUM(waktu) as count"));
            //         $query->whereMonth('tanggal',$bulan);
            //     }])
            //     ->join('tb_jabatan','tb_jabatan.id_pegawai','=','tb_pegawai.id')
            //     ->where('tb_pegawai.id_satuan_kerja',27)
            //                // ->with('aktivitas')
            //     ->get();


            $get_kinerja = aktivitas::query()
                           ->select(DB::raw("SUM(waktu) as count"))
                           ->where('id_pegawai',$value->id)
                           ->whereMonth('tanggal',$bulan)
                           ->first();
            $value->get_kinerja = $get_kinerja;
        
            $getAbsenPegawai = DB::table('tb_absen')
                ->select('id', 'id_pegawai', 'waktu_absen', 'status', 'jenis', 'tanggal_absen')
                ->where('id_pegawai', $value->id)
                ->where('tanggal_absen', '>=', $startDate)
                ->where('tanggal_absen', '<=', $endDate)
                ->where('validation', 1)
                ->groupBy('tb_absen.tanggal_absen', 'tb_absen.jenis')
                ->get();

            if (count($getAbsenPegawai) > 0) {
                $value->absen = $getAbsenPegawai;
            } else {
                $value->absen = "-";
            }

            if (isset($getAbsenPegawai)) {
                $selisih_waktu = 0;
                $jml_hari_kerja = [];
                $kmk_30 = [];
                $kmk_60 = [];
                $kmk_90 = [];
                $kmk_90_keatas = [];
                $cpk_30 = [];
                $cpk_60 = [];
                $cpk_90 = [];
                $cpk_90_keatas = [];
                $date_val = array();
                $jml_tanpa_keterangan = 0;
                $nums = 0;

                foreach ($getAbsenPegawai as $key => $val) {
                    // return $val;
                    if (isset($val->status)) {

                        array_push($date_val, $val->tanggal_absen);

                        if ($val->jenis == 'checkin') {
                            $jml_hari_kerja[] = $val->id;
                            $selisih_waktu = (new laporanRekapitulasiabsenController)->konvertWaktu('checkin', $val->waktu_absen);

                            if ($selisih_waktu >= 1 && $selisih_waktu <= 30) {
                                $kmk_30[] = $selisih_waktu;
                            } elseif ($selisih_waktu >= 31 && $selisih_waktu <= 60) {
                                $kmk_60[] =  $selisih_waktu;
                            } elseif ($selisih_waktu >= 61 && $selisih_waktu <= 90) {
                                $kmk_90[] =  $selisih_waktu;
                            } elseif ($selisih_waktu >= 91) {
                                $kmk_90_keatas[] =  $selisih_waktu;
                            }
                        } else {

                            $selisih_waktu = (new laporanRekapitulasiabsenController)->konvertWaktu('checkout', $val->waktu_absen);

                            if ($selisih_waktu >= 1 && $selisih_waktu <= 30) {
                                $cpk_30[] = $selisih_waktu;
                            } elseif ($selisih_waktu >= 31 && $selisih_waktu <= 60) {
                                $cpk_60[] =  $selisih_waktu;
                            } elseif ($selisih_waktu >= 61 && $selisih_waktu <= 90) {
                                $cpk_90[] =  $selisih_waktu;
                            } elseif ($selisih_waktu >= 91) {
                                $cpk_90_keatas[] =  $selisih_waktu;
                            }
                        }
                    }
                }


                foreach ($range['hari_kerja'] as $key => $val) {
                    if (in_array($val, $date_val) == false) {
                        $jml_tanpa_keterangan += $nums + 1;
                    }
                }
                $jml_potongan_kehadiran = ($jml_tanpa_keterangan * 3) + (count($kmk_30) * 0.5) + (count($kmk_60)) + (count($kmk_90) * 1.25) + (count($kmk_90_keatas) * 1.5) + (count($cpk_30) * 0.5) + (count($cpk_60)) + (count($cpk_90) * 1.25) + count($cpk_90_keatas) * 1.5;

                $persentasePemotonganKehadiran = $jml_potongan_kehadiran * 0.4;

                $value->persentase_pemotongan = round($persentasePemotonganKehadiran, 1);
                $value->jumlah_alpa = $jml_tanpa_keterangan;
            }
        }

        $result['satuan_kerja'] = $namaSatuanKerja->nama_satuan_kerja;
        $result['list_pegawai'] = $pegawaiBySatuanKerja;

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
}
