<?php

namespace App\Http\Controllers;

use App\Models\absen;
use Illuminate\Http\Request;
use App\Models\pegawai;
use App\Models\skp;
use DB;
use Auth;

class laporanRekapitulasiTppController extends Controller
{

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
            $pegawaiBySatuanKerja = DB::table('tb_pegawai')->select('tb_pegawai.id', 'tb_pegawai.nama', 'tb_pegawai.nip', 'tb_pegawai.golongan', 'tb_pegawai.jenis_jabatan', 'tb_jabatan.nama_jabatan', 'tb_jabatan.nilai_jabatan', 'tb_jabatan.id_jenis_jabatan', 'tb_jenis_jabatan.level')
                ->join('tb_jabatan', 'tb_pegawai.id', '=', 'tb_jabatan.id_pegawai')
                ->join('tb_jenis_jabatan', 'tb_jabatan.id_jenis_jabatan', '=', 'tb_jenis_jabatan.id')
                ->where('tb_pegawai.id_satuan_kerja', $satuanKerja)
                // ->where('tb_pegawai.id', 256)
                ->orderBy('tb_jabatan.id_jenis_jabatan', 'asc')
                ->get();
        } else {
            $pegawai = pegawai::where('id', Auth::user()->id_pegawai)->first();

            $pegawaiBySatuanKerja = DB::table('tb_pegawai')->select('tb_pegawai.id', 'tb_pegawai.nama', 'tb_pegawai.nip', 'tb_pegawai.golongan', 'tb_pegawai.jenis_jabatan', 'tb_jabatan.nama_jabatan', 'tb_jabatan.nilai_jabatan', 'tb_jabatan.id_jenis_jabatan', 'tb_jenis_jabatan.level')
                ->join('tb_jabatan', 'tb_pegawai.id', '=', 'tb_jabatan.id_pegawai')
                ->join('tb_jenis_jabatan', 'tb_jabatan.id_jenis_jabatan', '=', 'tb_jenis_jabatan.id')
                ->where('id_satuan_kerja', $pegawai->id_satuan_kerja)
                ->orderBy('nama', 'asc')
                ->get();
        }

        foreach ($pegawaiBySatuanKerja as $key => $value) {

            $result = [];
            $jabatanByPegawai = DB::table('tb_jabatan')->where('id_pegawai', $value->id)->first();

            $get_skp =
                skp::select('tb_skp.id', 'tb_skp.id_jabatan', 'tb_skp.id_satuan_kerja', 'tb_skp.id_skp_atasan', 'tb_skp.jenis', 'tb_skp.rencana_kerja', 'tb_skp.tahun')
                ->with(['aspek_skp' => function ($query) use ($bulan) {
                    $query
                        ->select('tb_aspek_skp.id', 'tb_aspek_skp.id_skp', 'tb_aspek_skp.iki', 'tb_aspek_skp.aspek_skp', 'tb_aspek_skp.satuan')->with(['target_skp' => function ($select) use ($bulan) {
                            $select->select('tb_target_skp.id', 'tb_target_skp.id_aspek_skp', 'tb_target_skp.target', 'tb_target_skp.bulan')->where('bulan', "{$bulan}");
                        }])
                        ->with(['realisasi_skp' => function ($select) use ($bulan) {
                            $select->select('tb_realisasi_skp.id', 'tb_realisasi_skp.id_aspek_skp', 'tb_realisasi_skp.realisasi_bulanan', 'tb_realisasi_skp.bulan')->where('bulan', "{$bulan}");
                        }]);
                }])
                ->whereHas('aspek_skp', function ($query) use ($bulan) {
                    $query->whereHas('target_skp', function ($query) use ($bulan) {
                        $query->where('bulan', "{$bulan}");
                    });
                })
                ->where('id_jabatan', $jabatanByPegawai->id)
                ->whereHas('aspek_skp', function ($query) use ($bulan) {
                    $query->whereHas('target_skp', function ($query) use ($bulan) {
                        $query->where('bulan', '' . $bulan . '');
                    });
                })
                ->orderBy('jenis')
                ->get();



            $value->skp = $get_skp;

            if (isset($get_skp)) {

                $nilai_utama = 0;
                $nilai_tambahan = 0;

                $total_utama = 0;
                $total_tambahan = 0;

                $data_utama = 0;
                $index_data = 0;

                $jumlah_data = 0;
                $sum_nilai_iki = 0;

                if ($value->level == 1 || $value->level == 2) {
                    foreach ($get_skp as $index => $val) {


                        // cek if isset skp_utama
                        if ($val->jenis == "utama") {

                            $index_data++;
                            $data_utama++;

                            $sum_capaian = 0;
                            foreach ($val->aspek_skp as $key => $v) {

                                foreach ($v['target_skp'] as $mk => $rr) {
                                    $kategori_ = '';
                                    if ($rr['bulan'] ==  $bulan) {

                                        $single_rate = ($v['realisasi_skp'][$mk]['realisasi_bulanan'] / $rr['target']) * 100;

                                        if ($single_rate > 110) {
                                            $nilai_iki = 110 + ((120 - 110) / (110 - 101)) * (110 - 101);
                                        } elseif ($single_rate >= 101 && $single_rate <= 110) {
                                            $nilai_iki = 110 + ((120 - 110) / (110 - 101)) * ($single_rate - 101);
                                        } elseif ($single_rate == 100) {
                                            $nilai_iki = 109;
                                        } elseif ($single_rate >= 80 && $single_rate <= 99) {
                                            $nilai_iki = 70 + ((89 - 70) / (99 - 80)) * ($single_rate - 80);
                                        } elseif ($single_rate >= 60 && $single_rate <= 79) {
                                            $nilai_iki = 50 + ((69 - 50) / (79 - 60)) * ($single_rate - 60);
                                        } elseif ($single_rate >= 0 && $single_rate <= 79) {
                                            $nilai_iki = (49 / 59) * $single_rate;
                                        }

                                        $sum_nilai_iki += $nilai_iki;
                                        $jumlah_data++;
                                    }
                                }
                            }


                            // cek if total_utama & data_utama != 0
                            if ($sum_nilai_iki != 0 && $jumlah_data != 0) {
                                $nilai_utama = round($sum_nilai_iki / $jumlah_data, 1);
                            } else {
                                $nilai_utama = 0;
                            }
                        } elseif ($val->jenis == "tambahan") {

                            $sum_capaian = 0;
                            foreach ($val->aspek_skp as $k => $v) {

                                foreach ($v['target_skp'] as $mk => $rr) {
                                    $kategori_ = '';
                                    if ($rr['bulan'] ==  $bulan) {

                                        $single_rate = ($v['realisasi_skp'][$mk]['realisasi_bulanan'] / $rr['target']) * 100;

                                        if ($single_rate > 110) {
                                            $nilai_iki = 110 + ((120 - 110) / (110 - 101)) * (110 - 101);
                                        } elseif ($single_rate >= 101 && $single_rate <= 110) {
                                            $nilai_iki = 110 + ((120 - 110) / (110 - 101)) * ($single_rate - 101);
                                        } elseif ($single_rate == 100) {
                                            $nilai_iki = 109;
                                        } elseif ($single_rate >= 80 && $single_rate <= 99) {
                                            $nilai_iki = 70 + ((89 - 70) / (99 - 80)) * ($single_rate - 80);
                                        } elseif ($single_rate >= 60 && $single_rate <= 79) {
                                            $nilai_iki = 50 + ((69 - 50) / (79 - 60)) * ($single_rate - 60);
                                        } elseif ($single_rate >= 0 && $single_rate <= 79) {
                                            $nilai_iki = (49 / 59) * $single_rate;
                                        }

                                        if ($nilai_iki > 110) {
                                            $total_tambahan += 2.4;
                                        } elseif ($nilai_iki >= 101 && $nilai_iki <= 110) {
                                            $total_tambahan += 1.6;
                                        } elseif ($nilai_iki == 100) {
                                            $total_tambahan += 1.0;
                                        } elseif ($nilai_iki >= 80 && $nilai_iki <= 99) {
                                            $total_tambahan += 0.5;
                                        } elseif ($nilai_iki >= 60 && $nilai_iki <= 79) {
                                            $total_tambahan += 0.3;
                                        } elseif ($nilai_iki >= 0 && $nilai_iki <= 79) {
                                            $total_tambahan += 0.1;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    if ($sum_nilai_iki != 0 && $jumlah_data != 0) {
                        $nilai_utama = round($sum_nilai_iki / $jumlah_data, 1);
                    } else {
                        $nilai_utama = 0;
                    }

                    $nilai_tambahan = $total_tambahan;
                } else {
                    foreach ($get_skp as $index => $val) {

                        // cek if isset skp_utama
                        if ($val->jenis == "utama") {

                            $index_data++;
                            $data_utama++;

                            $sum_capaian = 0;
                            foreach ($val->aspek_skp as $key => $v) {

                                foreach ($v['target_skp'] as $mk => $rr) {

                                    $kategori_ = '';
                                    if ($rr['bulan'] ==  $bulan) {
                                        // set capaian_iki based realisasi / target
                                        $capaian_iki = ($v['realisasi_skp'][$mk]['realisasi_bulanan'] / $rr['target']) * 100;

                                        // set nilai_iki based capaian_iki
                                        if ($capaian_iki >= 101) {
                                            $nilai_iki = 16;
                                        } elseif ($capaian_iki == 100) {
                                            $nilai_iki = 13;
                                        } elseif ($capaian_iki >= 80 && $capaian_iki <= 99) {
                                            $nilai_iki = 8;
                                        } elseif ($capaian_iki >= 60 && $capaian_iki <= 79) {
                                            $nilai_iki = 3;
                                        } elseif ($capaian_iki >= 0 && $capaian_iki <= 79) {
                                            $nilai_iki = 1;
                                        }
                                        $sum_capaian += $nilai_iki;
                                    }
                                }
                            }

                            // set total_utama based sum_capaian
                            if ($sum_capaian > 42) {
                                $total_utama += 120;
                            } elseif ($sum_capaian >= 34) {
                                $total_utama += 100;
                            } elseif ($sum_capaian >= 19) {
                                $total_utama += 80;
                            } elseif ($sum_capaian >= 7) {
                                $total_utama += 60;
                            } elseif ($sum_capaian >= 3) {
                                $total_utama += 25;
                            } elseif ($sum_capaian >= 0) {
                                $total_utama += 25;
                            }


                            // cek if total_utama & data_utama != 0
                            if ($total_utama != 0 && $data_utama != 0) {
                                $nilai_utama = round($total_utama / $data_utama, 1);
                            } else {
                                $nilai_utama = 0;
                            }
                        } elseif ($val->jenis == "tambahan") {

                            $sum_capaian = 0;
                            foreach ($val->aspek_skp as $k => $v) {

                                foreach ($v['target_skp'] as $mk => $rr) {
                                    if ($rr['bulan'] ==  $bulan) {

                                        $capaian_iki = ($v['realisasi_skp'][$mk]['realisasi_bulanan'] / $rr['target']) * 100;

                                        if ($capaian_iki >= 101) {
                                            $nilai_iki = 16;
                                        } elseif ($capaian_iki == 100) {
                                            $nilai_iki = 13;
                                        } elseif ($capaian_iki >= 80 && $capaian_iki <= 99) {
                                            $nilai_iki = 8;
                                        } elseif ($capaian_iki >= 60 && $capaian_iki <= 79) {
                                            $nilai_iki = 3;
                                        } elseif ($capaian_iki >= 0 && $capaian_iki <= 79) {
                                            $nilai_iki = 1;
                                        }
                                        $sum_capaian += $nilai_iki;
                                    }
                                }
                            }


                            if ($sum_capaian >= 42) {
                                $total_tambahan += 2.4;
                            } elseif ($sum_capaian >= 34) {
                                $total_tambahan += 1.6;
                            } elseif ($sum_capaian >= 19) {
                                $total_tambahan += 1;
                            } elseif ($sum_capaian >= 7) {
                                $total_tambahan += 0.5;
                            } elseif ($sum_capaian >= 3) {
                                $total_tambahan += 0.1;
                            } elseif ($sum_capaian >= 0) {
                                $total_tambahan += 0.1;
                            }
                        }
                    }

                    // cek if total_utama & data_utama != 0
                    if ($total_utama != 0 && $data_utama != 0) {
                        $nilai_utama = round($total_utama / $data_utama, 1);
                    } else {
                        $nilai_utama = 0;
                    }

                    $nilai_tambahan = $total_tambahan;
                }
            }

            $total_kinerja = round($nilai_utama + $nilai_tambahan, 1);

            $value->total_kinerja = $total_kinerja;

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
