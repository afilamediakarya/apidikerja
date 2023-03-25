<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\skp;
use App\Models\aspek_skp;
use App\Models\target_skp;
use App\Models\atasan;
use App\Models\review_realisasi_skp;
use App\Models\review_skp;
use App\Models\realisasi_skp;
use App\Models\satuan;
use App\Models\jabatan;
use App\Models\kegiatan;
use App\Models\pegawai;
use App\Models\aktivitas;
use DB;
use Validator;
use Auth;

class laporanController extends Controller
{
    public function cekLevel($id_pegawai)
    {
        $jabatan = jabatan::with('jenis_jabatan')->select('id', 'id_jenis_jabatan')->where('id_pegawai', $id_pegawai)->get();
        if (count($jabatan) > 0) {
            foreach ($jabatan as $key => $value) {
                if (!is_null($value['jenis_jabatan'])) {

                    $level_[] = $value['jenis_jabatan']['level'];
                } else {
                    // $level_[] = 0;
                    $status_fails = 'Jabatan tidak di temukan, Mohon hubungi admin opd';
                }
            }
        } else {
            // $level_[] = 0;
            $status_fails = 'Jabatan tidak di temukan, Mohon hubungi admin opd';
            return response()->json([
                // 'message' => 'Gagal Login',
                'messages_' => $status_fails
            ], 422);
        }

        if (count($level_) > 0) {
            $level = max($level_);
        }
        // else {
        //     $level = 0;
        // }

        if ($level !== '') {
            return response()->json([
                'level_jabatan' => $level,
            ]);
        } else {
            return response()->json([
                'message' => 'Gagal Login',
                'messages_' => $status_fails
            ], 422);
        }
    }
    public function laporanRekapitulasiSkp(Request $request, $bulan)
    {
        $result = [];
        $adminOpd = DB::table('tb_pegawai')->where('id', Auth::user()->id_pegawai)->first();

        $idSatuanKerja = (request('dinas') !== null) ? request('dinas') : $adminOpd->id_satuan_kerja;

        $satuanKerja = DB::table('tb_satuan_kerja')
            ->select('nama_satuan_kerja')
            ->where('id', $idSatuanKerja)
            ->first();

        $listPegawai = DB::table('tb_pegawai')
            ->select('tb_pegawai.id', 'tb_pegawai.nama', 'tb_pegawai.nip', 'tb_pegawai.golongan', 'tb_jabatan.id as id_jabatan', 'tb_jabatan.nama_jabatan', 'tb_jabatan.id_jenis_jabatan', 'tb_jabatan.parent_id', 'tb_jenis_jabatan.level','tb_jabatan.kelas_jabatan')
            ->join('tb_jabatan', 'tb_pegawai.id', '=', 'tb_jabatan.id_pegawai')
            ->join('tb_jenis_jabatan', 'tb_jabatan.id_jenis_jabatan', '=', 'tb_jenis_jabatan.id')
            ->where('tb_pegawai.id_satuan_kerja', $idSatuanKerja)
            ->orderBy('tb_jabatan.kelas_jabatan', 'DESC')
            ->get();

        foreach ($listPegawai as $key => $value) {
            $result = [];
            $jabatanByPegawai = DB::table('tb_jabatan')->where('id_pegawai', $value->id)->first();
            $skp_utama =
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
                ->where('jenis', 'utama')
                ->where('id_jabatan', $jabatanByPegawai->id)
                ->whereHas('aspek_skp', function ($query) use ($bulan) {
                    $query->whereHas('target_skp', function ($query) use ($bulan) {
                        $query->where('bulan', '' . $bulan . '');
                    });
                })
                ->get();

            $skp_tambahan =
                skp::with(['aspek_skp' => function ($query) use ($bulan) {
                    $query->with(['target_skp' => function ($select) use ($bulan) {
                        $select->where('bulan', "{$bulan}");
                    }])
                        ->with(['realisasi_skp' => function ($select) use ($bulan) {
                            $select->where('bulan', "{$bulan}");
                        }]);
                }])
                ->whereHas('aspek_skp', function ($query) use ($bulan) {
                    $query->whereHas('target_skp', function ($query) use ($bulan) {
                        $query->where('bulan', "{$bulan}");
                    });
                })
                ->where('jenis', 'tambahan')
                ->where('id_jabatan', $jabatanByPegawai->id)
                ->whereHas('aspek_skp', function ($query) use ($bulan) {
                    $query->whereHas('target_skp', function ($query) use ($bulan) {
                        $query->where('bulan', '' . $bulan . '');
                    });
                })
                ->get();

            $value->skp_utama = $skp_utama;
            $value->skp_tambahan = $skp_tambahan;

            // if ($jabatan['jenis_jabatan']['level'] == 1 || $jabatan['jenis_jabatan']['level'] == 2) {
            //     $result = [];
            //     $skp = [];
            //     $atasan = '';
            //     $jabatanByPegawai = DB::table('tb_jabatan')->where('id_pegawai', $value->id)->first();

            //     $jabatan_atasan = DB::table('tb_jabatan')->where('id', $value->parent_id)->first();

            //     $skp_utama =
            //         skp::with(['aspek_skp' => function ($query) use ($bulan) {
            //             $query->with(['target_skp' => function ($select) use ($bulan) {
            //                 $select->where('bulan', "{$bulan}");
            //             }])
            //                 ->with(['realisasi_skp' => function ($select) use ($bulan) {
            //                     $select->where('bulan', "{$bulan}");
            //                 }]);
            //         }])
            //         ->whereHas('aspek_skp', function ($query) use ($bulan) {
            //             $query->whereHas('target_skp', function ($query) use ($bulan) {
            //                 $query->where('bulan', "{$bulan}");
            //             });
            //         })
            //         ->where('jenis', 'utama')
            //         ->where('id_jabatan', $jabatanByPegawai->id)
            //         ->whereHas('aspek_skp', function ($query) use ($bulan) {
            //             $query->whereHas('target_skp', function ($query) use ($bulan) {
            //                 $query->where('bulan', '' . $bulan . '');
            //             });
            //         })
            //         ->get();

            //     $skp_tambahan =
            //         skp::with(['aspek_skp' => function ($query) use ($bulan) {
            //             $query->with(['target_skp' => function ($select) use ($bulan) {
            //                 $select->where('bulan', "{$bulan}");
            //             }])
            //                 ->with(['realisasi_skp' => function ($select) use ($bulan) {
            //                     $select->where('bulan', "{$bulan}");
            //                 }]);
            //         }])
            //         ->whereHas('aspek_skp', function ($query) use ($bulan) {
            //             $query->whereHas('target_skp', function ($query) use ($bulan) {
            //                 $query->where('bulan', "{$bulan}");
            //             });
            //         })
            //         ->where('jenis', 'tambahan')
            //         ->where('id_jabatan', $jabatanByPegawai->id)
            //         ->whereHas('aspek_skp', function ($query) use ($bulan) {
            //             $query->whereHas('target_skp', function ($query) use ($bulan) {
            //                 $query->where('bulan', '' . $bulan . '');
            //             });
            //         })
            //         ->get();

            //     $value->skp_utama = $skp_utama;
            //     $value->skp_tambahan = $skp_tambahan;
            // }
            // else {
            //     $get_skp_atasan = DB::table('tb_skp')->select('id_skp_atasan')->where('id_jabatan', $value->id_jabatan)->groupBy('tb_skp.id_skp_atasan')->get();

            //     $skp_utama = [];
            //     $skp_tambahan = [];
            //     foreach ($get_skp_atasan as $k => $v) {
            //         if (!is_null($value->parent_id)) {

            //             if (!is_null($v->id_skp_atasan)) {
            //                 $getSkpAtasan = DB::table('tb_skp')->select('id', 'rencana_kerja', 'jenis')->where('id', $v->id_skp_atasan)->first();
            //                 if (isset($getSkpAtasan)) {

            //                     $data_skp_utama =
            //                         skp::with(['aspek_skp' => function ($query) use ($bulan) {
            //                             $query->with(['target_skp' => function ($select) use ($bulan) {
            //                                 $select->where('bulan', "{$bulan}");
            //                             }])
            //                                 ->with(['realisasi_skp' => function ($select) use ($bulan) {
            //                                     $select->where('bulan', "{$bulan}");
            //                                 }]);
            //                         }])
            //                         ->where('id_skp_atasan', $getSkpAtasan->id)
            //                         ->where('jenis', 'utama')
            //                         ->where('id_jabatan', $value->id_jabatan)
            //                         ->whereHas('aspek_skp', function ($query) use ($bulan) {
            //                             $query->whereHas('target_skp', function ($query) use ($bulan) {
            //                                 $query->where('bulan', '' . $bulan . '');
            //                             });
            //                         })
            //                         ->first();

            //                     if ($data_skp_utama != []) {
            //                         $skpChild[] = $data_skp_utama;
            //                     } else {
            //                         $skpChild = [];
            //                     }

            //                     $skp_utama[$k] = [
            //                         'id_skp_atasan' => $getSkpAtasan->id,
            //                         'rencana_kerja_atasan' => $getSkpAtasan->rencana_kerja,
            //                         'skp_child' => $skpChild,
            //                     ];
            //                 } else {
            //                     $skp_utama = [];
            //                 }
            //             }
            //         }
            //     }

            //     // foreach ($get_skp_atasan as $k => $val) {
            //     //     $getRencanaKerjaAtasan = [];
            //     //     if (!is_null($value->parent_id)) {

            //     //         if (!is_null($value->id_skp_atasan)) {
            //     //             $getSkpAtasan = DB::table('tb_skp')->select('id', 'rencana_kerja', 'jenis')->where('id', $val->id_skp_atasan)->first();
            //     //             if (isset($getSkpAtasan)) {
            //     //                 $getRencanaKerjaAtasan = [
            //     //                     'id' => $getSkpAtasan->id,
            //     //                     'rencana_kerja' => $getSkpAtasan->rencana_kerja
            //     //                 ];
            //     //             }
            //     //         }
            //     //     }

            //     //     if ($getRencanaKerjaAtasan != []) {

            //     //         $skpChild =
            //     //             skp::with(['aspek_skp' => function ($query) use ($bulan) {
            //     //                 $query->with(['target_skp' => function ($select) use ($bulan) {
            //     //                     $select->where('bulan', "{$bulan}");
            //     //                 }])
            //     //                     ->with(['realisasi_skp' => function ($select) use ($bulan) {
            //     //                         $select->where('bulan', "{$bulan}");
            //     //                     }]);
            //     //             }])
            //     //             ->whereHas('aspek_skp', function ($query) use ($bulan) {
            //     //                 $query->whereHas('target_skp', function ($query) use ($bulan) {
            //     //                     $query->where('bulan', "{$bulan}");
            //     //                 });
            //     //             })
            //     //             ->where('id_skp_atasan', $getRencanaKerjaAtasan['id'])
            //     //             ->where('jenis', 'utama')
            //     //             ->where('id_jabatan', $value->id_jabatan)
            //     //             ->get();
            //     //         // return $skpChild;
            //     //     } else {
            //     //         $skpChild = [];
            //     //     }


            //     //     if (count($getRencanaKerjaAtasan) > 0 && count($skpChild) > 0) {
            //     //         $result['skp']['utama'][$key]['atasan'] = $getRencanaKerjaAtasan;
            //     //         $result['skp']['utama'][$key]['skp_child'] = $skpChild;
            //     //     }
            //     // }

            //     $skp_tambahan =
            //         skp::with(['aspek_skp' => function ($query) use ($bulan) {
            //             $query->with(['target_skp' => function ($select) use ($bulan) {
            //                 $select->where('bulan', "{$bulan}");
            //             }])
            //                 ->with(['realisasi_skp' => function ($select) use ($bulan) {
            //                     $select->where('bulan', "{$bulan}");
            //                 }]);
            //         }])
            //         ->whereHas('aspek_skp', function ($query) use ($bulan) {
            //             $query->whereHas('target_skp', function ($query) use ($bulan) {
            //                 $query->where('bulan', "{$bulan}");
            //             });
            //         })
            //         ->where('jenis', 'tambahan')
            //         ->where('id_jabatan', $value->id)
            //         ->whereHas('aspek_skp', function ($query) use ($bulan) {
            //             $query->whereHas('target_skp', function ($query) use ($bulan) {
            //                 $query->where('bulan', '' . $bulan . '');
            //             });
            //         })
            //         ->get();

            //     $value->skp_utama = $skp_utama;
            //     $value->skp_tambahan = $skp_tambahan;
            // }
            // return $value;
        }

        $result['satuan_kerja'] = $satuanKerja;
        $result['list_pegawai'] = $listPegawai;

        if ($result) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $result
            ]);
        } else {
            return response()->json([
                'message' => 'empty data',
                'status' => false,
                'data' => $result
            ]);
        }
    }
    public function laporanSkp($params, $bulan, $id_pegawai)
    {
        if ($params == 'kepala') {
            return $this->laporanSkpKepala($bulan, $id_pegawai);
        } else {
            return $this->laporanSkpPegawai($bulan, $id_pegawai);
        }
    }

    public function laporanSkpKepala($bulan, $id_pegawai)
    {
        $result = [];
        $skp = [];
        $atasan = '';
        $jabatanByPegawai = DB::table('tb_jabatan')->where('id_pegawai', $id_pegawai)->first();

        $jabatan_atasan = DB::table('tb_jabatan')->where('id', $jabatanByPegawai->parent_id)->first();

        if (isset($jabatan_atasan->id_pegawai)) {
            $atasan = DB::table('tb_jabatan')->select('tb_pegawai.nama', 'tb_pegawai.nip', 'tb_pegawai.golongan', 'tb_jabatan.nama_jabatan', 'tb_satuan_kerja.nama_satuan_kerja')->join('tb_pegawai', 'tb_pegawai.id', '=', 'tb_jabatan.id_pegawai')->join('tb_satuan_kerja', 'tb_satuan_kerja.id', '=', 'tb_pegawai.id_satuan_kerja')->where('tb_pegawai.id', $jabatan_atasan->id_pegawai)->first();
        }

        $current = DB::table('tb_jabatan')->select('tb_pegawai.nama', 'tb_pegawai.nip', 'tb_pegawai.golongan', 'tb_jabatan.nama_jabatan', 'tb_satuan_kerja.nama_satuan_kerja')->join('tb_pegawai', 'tb_pegawai.id', '=', 'tb_jabatan.id_pegawai')->join('tb_satuan_kerja', 'tb_satuan_kerja.id', '=', 'tb_pegawai.id_satuan_kerja')->where('tb_pegawai.id', $id_pegawai)->first();

        // $skp = skp::with('aspek_skp')->where('id_pegawai',Auth::user()->id_pegawai)->get();
        // $skpUtama = skp::with('aspek_skp')->where('jenis', 'utama')->where('id_jabatan', $jabatanByPegawai->id)->get();
        // $skpTambahan = skp::with('aspek_skp')->where('jenis', 'tambahan')->where('id_jabatan', $jabatanByPegawai->id)->get();
        $skpUtama =
            skp::with(['aspek_skp' => function ($query) use ($bulan) {
                $query->with(['target_skp' => function ($select) use ($bulan) {
                    $select->where('bulan', "{$bulan}");
                }])
                    ->with(['realisasi_skp' => function ($select) use ($bulan) {
                        $select->where('bulan', "{$bulan}");
                    }]);
            }])
            ->whereHas('aspek_skp', function ($query) use ($bulan) {
                $query->whereHas('target_skp', function ($query) use ($bulan) {
                    $query->where('bulan', "{$bulan}");
                });
            })
            ->where('jenis', 'utama')
            ->where('id_jabatan', $jabatanByPegawai->id)
            ->whereHas('aspek_skp', function ($query) use ($bulan) {
                $query->whereHas('target_skp', function ($query) use ($bulan) {
                    $query->where('bulan', '' . $bulan . '');
                });
            })
            ->get();

        $skpTambahan =
            skp::with(['aspek_skp' => function ($query) use ($bulan) {
                $query->with(['target_skp' => function ($select) use ($bulan) {
                    $select->where('bulan', "{$bulan}");
                }])
                    ->with(['realisasi_skp' => function ($select) use ($bulan) {
                        $select->where('bulan', "{$bulan}");
                    }]);
            }])
            ->whereHas('aspek_skp', function ($query) use ($bulan) {
                $query->whereHas('target_skp', function ($query) use ($bulan) {
                    $query->where('bulan', "{$bulan}");
                });
            })
            ->where('jenis', 'tambahan')
            ->where('id_jabatan', $jabatanByPegawai->id)
            ->whereHas('aspek_skp', function ($query) use ($bulan) {
                $query->whereHas('target_skp', function ($query) use ($bulan) {
                    $query->where('bulan', '' . $bulan . '');
                });
            })
            ->get();

        $result['atasan'] = $atasan;
        $result['pegawai_dinilai'] = $current;
        $result['skp']['utama'] = $skpUtama;
        $result['skp']['tambahan'] = $skpTambahan;

        if ($result) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $result
            ]);
        } else {
            return response()->json([
                'message' => 'empty data',
                'status' => false,
                'data' => $result
            ]);
        }
    }

    public function laporanSkpPegawai($bulan, $id_pegawai)
    {

        $result = [];
        $groupSkpAtasan = [];
        $skpChild = '';
        $atasan = '';
        $jabatanByPegawai = DB::table('tb_jabatan')->where('id_pegawai', $id_pegawai)->first();

        if (isset($jabatanByPegawai)) {
            $jabatan_atasan = DB::table('tb_jabatan')->where('id', $jabatanByPegawai->parent_id)->first();
        }


        if (isset($jabatan_atasan->id_pegawai)) {
            $atasan = DB::table('tb_jabatan')->select('tb_pegawai.nama', 'tb_pegawai.nip', 'tb_pegawai.golongan', 'tb_jabatan.nama_jabatan', 'tb_satuan_kerja.nama_satuan_kerja')->join('tb_pegawai', 'tb_pegawai.id', '=', 'tb_jabatan.id_pegawai')->join('tb_satuan_kerja', 'tb_satuan_kerja.id', '=', 'tb_pegawai.id_satuan_kerja')->where('tb_pegawai.id', $jabatan_atasan->id_pegawai)->first();
        }

        $current = DB::table('tb_jabatan')->select('tb_pegawai.nama', 'tb_pegawai.nip', 'tb_pegawai.golongan', 'tb_jabatan.nama_jabatan', 'tb_satuan_kerja.nama_satuan_kerja')->join('tb_pegawai', 'tb_pegawai.id', '=', 'tb_jabatan.id_pegawai')->join('tb_satuan_kerja', 'tb_satuan_kerja.id', '=', 'tb_pegawai.id_satuan_kerja')->where('tb_pegawai.id', $id_pegawai)->first();

        $get_skp_atasan = DB::table('tb_skp')->select('id_skp_atasan')->where('id_jabatan', $jabatanByPegawai->id)->groupBy('tb_skp.id_skp_atasan')->get();



        $result['atasan'] = $atasan;
        $result['pegawai_dinilai'] = $current;


        foreach ($get_skp_atasan as $key => $value) {

            $getRencanaKerjaAtasan = [];

            if (!is_null($jabatanByPegawai->parent_id)) {

                if (!is_null($value->id_skp_atasan)) {
                    $getSkpAtasan = DB::table('tb_skp')->select('id', 'rencana_kerja', 'jenis')->where('id', $value->id_skp_atasan)->first();
                    if (isset($getSkpAtasan)) {
                        $getRencanaKerjaAtasan = [
                            'id' => $getSkpAtasan->id,
                            'rencana_kerja' => $getSkpAtasan->rencana_kerja
                        ];
                    }
                } else {
                    $getRencanaKerjaAtasan = [
                        'id' => null,
                        'rencana_kerja' => "-",
                    ];
                }
            } else {
                // $getKegiatan= DB::table('tb_kegiatan')->select('id','nama_kegiatan','kode_kegiatan')->where('id',$value->id_skp_atasan)->first();

                // if (isset($getKegiatan)) {
                //    $getRencanaKerjaAtasan = [
                //        'id' => $getKegiatan->id,
                //        'rencana_kerja' =>$getKegiatan->nama_kegiatan
                //     ];
                // }


            }

            if ($getRencanaKerjaAtasan != []) {
                // $skpChild = skp::with('aspek_skp')->where('id_skp_atasan', $getRencanaKerjaAtasan['id'])->where('id_jabatan', $jabatanByPegawai->id)->where('jenis', 'utama')->get();
                $skpChild =
                    skp::with(['aspek_skp' => function ($query) use ($bulan) {
                        $query->with(['target_skp' => function ($select) use ($bulan) {
                            $select->where('bulan', "{$bulan}");
                        }])
                            ->with(['realisasi_skp' => function ($select) use ($bulan) {
                                $select->where('bulan', "{$bulan}");
                            }]);
                    }])
                    ->whereHas('aspek_skp', function ($query) use ($bulan) {
                        $query->whereHas('target_skp', function ($query) use ($bulan) {
                            $query->where('bulan', "{$bulan}");
                        });
                    })
                    ->where('id_skp_atasan', $getRencanaKerjaAtasan['id'])
                    ->where('jenis', 'utama')
                    ->where('id_jabatan', $jabatanByPegawai->id)
                    ->get();
                // return $skpChild;
            } else {
                $skpChild =
                    skp::with(['aspek_skp' => function ($query) use ($bulan) {
                        $query->with(['target_skp' => function ($select) use ($bulan) {
                            $select->where('bulan', "{$bulan}");
                        }])
                            ->with(['realisasi_skp' => function ($select) use ($bulan) {
                                $select->where('bulan', "{$bulan}");
                            }]);
                    }])
                    ->whereHas('aspek_skp', function ($query) use ($bulan) {
                        $query->whereHas('target_skp', function ($query) use ($bulan) {
                            $query->where('bulan', "{$bulan}");
                        });
                    })
                    ->where('jenis', 'utama')
                    ->where('id_jabatan', $jabatanByPegawai->id)
                    ->get();
            }


            if (count($getRencanaKerjaAtasan) > 0 && count($skpChild) > 0) {
                $result['skp']['utama'][$key]['atasan'] = $getRencanaKerjaAtasan;
                $result['skp']['utama'][$key]['skp_child'] = $skpChild;
            }
        }

        // $skp_tambahan = skp::with('aspek_skp')->where('jenis', 'tambahan')->where('id_jabatan', $jabatanByPegawai->id)->get();

        $skp_tambahan =
            skp::with(['aspek_skp' => function ($query) use ($bulan) {
                $query->with(['target_skp' => function ($select) use ($bulan) {
                    $select->where('bulan', "{$bulan}");
                }])
                    ->with(['realisasi_skp' => function ($select) use ($bulan) {
                        $select->where('bulan', "{$bulan}");
                    }]);
            }])
            ->whereHas('aspek_skp', function ($query) use ($bulan) {
                $query->whereHas('target_skp', function ($query) use ($bulan) {
                    $query->where('bulan', "{$bulan}");
                });
            })
            // ->where('id_skp_atasan', $getRencanaKerjaAtasan['id'])
            ->where('jenis', 'tambahan')
            ->where('id_jabatan', $jabatanByPegawai->id)
            ->get();

        if (count($skp_tambahan) > 0) {
            $result['skp']['tambahan'] = $skp_tambahan;
        }


        if ($result) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $result
            ]);
        } else {
            return response()->json([
                'message' => 'empty data',
                'status' => false,
                'data' => $result
            ]);
        }
    }

    private function jabatanByPegawai($params,$type){

        $query = '';

        $type == 'pegawai' ? $query = 'tb_jabatan.id_pegawai' : $query = 'tb_jabatan.id';

        // return $query;

      $data =  DB::table('tb_jabatan')
        ->select('tb_jabatan.id', 'tb_jabatan.id_pegawai','tb_jabatan.parent_id','tb_jabatan.nama_jabatan','tb_pegawai.nama as nama_pegawai','tb_pegawai.nip','tb_pegawai.golongan','tb_satuan_kerja.nama_satuan_kerja','tb_jabatan.target_waktu')
        ->join('tb_pegawai','tb_jabatan.id_pegawai', '=', 'tb_pegawai.id')
        ->join('tb_satuan_kerja','tb_pegawai.id_satuan_kerja','=','tb_satuan_kerja.id')
        ->whereRaw($query."=".$params)->first();

        return $data;

    }

    public function kinerja(){
        $result = array();
        $pegawai_dinilai = array();
        $pegawai_penilai = array();
        $bulan = request('bulan');

        $current_pegawai =  $this->jabatanByPegawai(Auth::user()->id_pegawai,'pegawai');
        $atasan = $this->jabatanByPegawai($current_pegawai->parent_id,'atasan');

        $pegawai_dinilai = [
            'nama' => $current_pegawai->nama_pegawai,
            'nip' => $current_pegawai->nip,
            'golongan' => $current_pegawai->golongan,
            'jabatan' => $current_pegawai->nama_jabatan,
            'unit_kerja' => $current_pegawai->nama_satuan_kerja,
            'waktu' => $current_pegawai->target_waktu
        ];

        $pegawai_penilai = [
            'nama' => $atasan->nama_pegawai,
            'nip' => $atasan->nip,
            'golongan' => $atasan->golongan,
            'jabatan' => $atasan->nama_jabatan,
            'unit_kerja' => $atasan->nama_satuan_kerja
        ];


        // $data = skp::where('id_jabatan',$current_pegawai->id)->get();

        $data = skp::query()
                ->select('id','id_satuan_kerja','rencana_kerja','tahun')
                ->with(['aktivitas'=> function($query) use ($bulan) {
                    $query->select('id','nama_aktivitas','id_skp','satuan','tanggal','created_at','keterangan',DB::raw('sum(hasil) as hasil'),DB::raw('sum(waktu) as waktu'));
                    $query->whereMonth('tanggal',$bulan);
                    $query->groupBy('id_skp','tanggal','nama_aktivitas');
                    $query->orderBy('tanggal','ASC');
                }])
                ->where('tahun',date('Y'))
                ->where('id_jabatan',$current_pegawai->id)
                ->orderBy('created_at','DESC')
                ->get();      


        $result = [
            'pegawai_dinilai' => $pegawai_dinilai,
            'pegawai_penilai' => $pegawai_penilai,
            'kinerja' =>$data
        ];

        return $result;
    }

     public function kinerjaView(){
        $result = array();
        $pegawai_dinilai = array();
        $pegawai_penilai = array();
        $bulan = request('bulan');

        $current_pegawai =  $this->jabatanByPegawai(request('pegawai'),'pegawai');
        $atasan = $this->jabatanByPegawai($current_pegawai->parent_id,'atasan');

        $pegawai_dinilai = [
            'nama' => $current_pegawai->nama_pegawai,
            'nip' => $current_pegawai->nip,
            'golongan' => $current_pegawai->golongan,
            'jabatan' => $current_pegawai->nama_jabatan,
            'unit_kerja' => $current_pegawai->nama_satuan_kerja,
            'waktu' => $current_pegawai->target_waktu
        ];

        $pegawai_penilai = [
            'nama' => $atasan->nama_pegawai,
            'nip' => $atasan->nip,
            'golongan' => $atasan->golongan,
            'jabatan' => $atasan->nama_jabatan,
            'unit_kerja' => $atasan->nama_satuan_kerja
        ];


        // $data = skp::where('id_jabatan',$current_pegawai->id)->get();

        $data = skp::query()
                ->select('id','id_satuan_kerja','rencana_kerja','tahun')
                ->with(['aktivitas'=> function($query) use ($bulan) {
                    $query->select('id','nama_aktivitas','id_skp','satuan','tanggal','created_at','keterangan',DB::raw('sum(hasil) as hasil'),DB::raw('sum(waktu) as waktu'));
                    $query->whereMonth('tanggal',$bulan);
                    $query->groupBy('id_skp','tanggal','nama_aktivitas');
                    $query->orderBy('tanggal','ASC');
                }])
                ->where('tahun',date('Y'))
                ->where('id_jabatan',$current_pegawai->id)
                ->orderBy('created_at','DESC')
                ->get();      


        $result = [
            'pegawai_dinilai' => $pegawai_dinilai,
            'pegawai_penilai' => $pegawai_penilai,
            'kinerja' =>$data
        ];

        return $result;
    }

    public function kinerjaByOpd(){
        $bulan = request('bulan');
        $satuanKerja = request('satuan_kerja');

        $pegawai =  pegawai::query()
                ->select('tb_pegawai.id','tb_pegawai.nama','tb_pegawai.nip','tb_pegawai.golongan','tb_jabatan.nama_jabatan','tb_jabatan.target_waktu','tb_jabatan.kelas_jabatan','tb_jenis_jabatan.level')
                ->join('tb_jabatan','tb_jabatan.id_pegawai','=','tb_pegawai.id')
                ->join('tb_jenis_jabatan','tb_jenis_jabatan.id','=','tb_jabatan.id_jenis_jabatan')
                ->where('tb_pegawai.id_satuan_kerja',$satuanKerja)
                ->orderBy('tb_jabatan.kelas_jabatan','DESC')
                ->orderBy('tb_pegawai.nama', 'ASC')
                //  ->with(['aktivitas'=> function($query) use ($bulan) {
                //     $query->select('id','id_pegawai','hasil',DB::raw("SUM(waktu) as count"));
                //     $query->whereMonth('tanggal',$bulan);
                //     // $query->where('id_pegawai',$)
                // }])

                ->get();

        foreach ($pegawai as $key => $value) {
            $aktivitas = DB::table('tb_aktivitas')->select('id','id_pegawai','hasil',DB::raw("SUM(waktu) as count"))->whereMonth('tanggal',$bulan)->where('id_pegawai',$value->id)->whereNotNull('id_pegawai')->get();           

            
            if ($aktivitas[0]->count !== null) {
                $value->aktivitas = $aktivitas;            
            }else{
                $value->aktivitas = [];            
            }
            
        }        

        return $pegawai;

    }
}
