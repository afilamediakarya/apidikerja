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
        }

        if (count($level_) > 0) {
            $level = max($level_);
        } else {
            $level = 0;
        }

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
    public function laporanRekapitulasiSkp($bulan)
    {
        $result = [];
        $adminOpd = DB::table('tb_pegawai')->where('id', Auth::user()->id_pegawai)->first();
        $satuanKerja = DB::table('tb_satuan_kerja')
            ->select('nama_satuan_kerja')
            ->where('id', $adminOpd->id_satuan_kerja)
            ->first();

        $listPegawai = DB::table('tb_pegawai')
            ->select('tb_pegawai.id', 'tb_pegawai.nama', 'tb_pegawai.nip', 'tb_pegawai.golongan', 'tb_jabatan.id as id_jabatan', 'tb_jabatan.nama_jabatan', 'tb_jabatan.parent_id')
            ->join('tb_jabatan', 'tb_pegawai.id', '=', 'tb_jabatan.id_pegawai')
            ->where('tb_pegawai.id_satuan_kerja', $adminOpd->id_satuan_kerja)
            // ->where('tb_pegawai.id', 32)
            ->get();

        // return $listPegawai;

        foreach ($listPegawai as $key => $value) {
            $jabatan = jabatan::with('jenis_jabatan')->select('id', 'id_jenis_jabatan')->where('id_pegawai', $value->id)->first();

            $result = [];
            $skp = [];
            $atasan = '';
            $jabatanByPegawai = DB::table('tb_jabatan')->where('id_pegawai', $value->id)->first();

            $jabatan_atasan = DB::table('tb_jabatan')->where('id', $value->parent_id)->first();

            $skp_utama =
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
                $skpChild = [];
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
            ->where('id_skp_atasan', $getRencanaKerjaAtasan['id'])
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
}
