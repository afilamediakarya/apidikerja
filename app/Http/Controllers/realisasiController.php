<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\realisasi_skp;
use App\Models\review_realisasi_skp;
use App\Models\atasan;
use App\Models\skp;
use Auth;
use Validator;
use DB;

class realisasiController extends Controller
{
    public function list()
    {
        $type = request('type');
        $tahun = request('tahun');
        $bulan = request('bulan');
        $result = array();
        $jabatanByPegawai =  DB::table('tb_jabatan')->select('tb_jabatan.id', 'tb_jabatan.id_pegawai')->join('tb_pegawai', 'tb_jabatan.id_pegawai', '=', 'tb_pegawai.id')->where('tb_jabatan.id_pegawai', Auth::user()->id_pegawai)->first();

        // $skp_filter =  DB::table('tb_skp')->select('tb_skp.id', 'tb_skp.id_skp_atasan')->join('tb_aspek_skp', 'tb_aspek_skp.id_skp', 'tb_skp.id')->join('tb_target_skp', 'tb_target_skp.id_aspek_skp', 'tb_aspek_skp.id')->groupBy('tb_skp.id')->where('tb_skp.tahun', $tahun)->where('id_jabatan', $jabatanByPegawai->id)->where('tb_target_skp.bulan', $bulan)->get();

        $skp_filter =  DB::table('tb_skp')->select('tb_skp.id', 'tb_skp.id_skp_atasan')->join('tb_aspek_skp', 'tb_aspek_skp.id_skp', 'tb_skp.id')->join('tb_target_skp', 'tb_target_skp.id_aspek_skp', 'tb_aspek_skp.id')->where('tb_skp.tahun', $tahun)->where('id_jabatan', $jabatanByPegawai->id)->where('tb_target_skp.bulan', $bulan)->orderBy('tb_skp.jenis', 'ASC')->orderBy('tb_skp.id', 'ASC')->get();

        if ($type == 'pegawai') {
            foreach ($skp_filter as $index => $val) {
                $data = skp::with('aspek_skp')->where('id', $val->id)->orderBy('jenis', 'ASC')->first();
                if (!is_null($val->id_skp_atasan)) {
                    $val->skp_atasan = DB::table('tb_skp')->where('id', $val->id_skp_atasan)->first()->rencana_kerja;
                } else {
                    $val->skp_atasan = '-';
                }
                // $data->skp_atasan = DB::table('tb_skp')->where('id',$val->id_skp_atasan)->first()->rencana_kerja;
                // return $data->reviewRealisasiSkp;
                $getReview = array();
                foreach ($data->reviewRealisasiSkp as $key => $value) {
                    if ($value->bulan == $bulan) {
                        array_push($getReview, $value->kesesuaian);
                        // $getReview = $value->pluck('kesesuaian')->toArray();
                        // $getReview = $value->kesesuaian;

                        // if (in_array("tidak", $getReview) == true && in_array("ya", $getReview) == true) {
                        //     $data->status_review = 'Belum Sesuai';
                        //     $data->keterangan = 'Penilai tidak menyetujui, karena tidak sesuai dengan realisasi';
                        //     $data->color = 'warning';
                        // } else if (in_array("ya", $getReview) == true && in_array("tidak", $getReview) == false) {

                        //     $data->status_review = 'Selesai';
                        //     $data->keterangan = 'Penilai telah menyetujui';
                        //     $data->color = 'success';
                        // } else {
                        //     $data->status_review = 'Belum Review';
                        //     $data->keterangan =  'Penilai belum melakukan review';
                        //     $data->color = 'danger';
                        // }
                    }
                }
                // return $getReview;

                if (in_array("tidak", $getReview) == true && in_array("ya", $getReview) == false) {
                    $data->status_review = 'Belum Sesuai';
                    $data->keterangan = 'Penilai tidak menyetujui, karena tidak sesuai dengan realisasi';
                    $data->color = 'warning';
                } else if (in_array("ya", $getReview) == true && in_array("tidak", $getReview) == false) {

                    $data->status_review = 'Selesai';
                    $data->keterangan = 'Penilai telah menyetujui';
                    $data->color = 'success';
                } else {
                    $data->status_review = 'Belum Review';
                    $data->keterangan =  'Penilai belum melakukan review';
                    $data->color = 'danger';
                }

                if ($data->jenis == 'utama') {
                    $data->jenis_kinerja = 'A. Kinerja Utama';
                } else {
                    $data->jenis_kinerja = 'B. Kinerja Tambahan';
                }



                $result[$index] = $data;
            }
        } else {
            foreach ($skp_filter as $index => $val) {
                $data = skp::with('aspek_skp')->where('id', $val->id)->orderBy('jenis', 'ASC')->first();

                $getReview = array();

                foreach ($data->reviewRealisasiSkp as $key => $value) {
                    $getReview = array();
                    foreach ($data->reviewRealisasiSkp as $key => $value) {
                        if ($value->bulan == $bulan) {
                            array_push($getReview, $value->kesesuaian);
                        }
                    }
                    // return $getReview;

                    if (in_array("tidak", $getReview) == true && in_array("ya", $getReview) == false) {
                        $data->status_review = 'Belum Sesuai';
                        $data->keterangan = 'Penilai tidak menyetujui, karena tidak sesuai dengan realisasi';
                        $data->color = 'warning';
                    } else if (in_array("ya", $getReview) == true && in_array("tidak", $getReview) == false) {

                        $data->status_review = 'Selesai';
                        $data->keterangan = 'Penilai telah menyetujui';
                        $data->color = 'success';
                    } else {
                        $data->status_review = 'Belum Review';
                        $data->keterangan =  'Penilai belum melakukan review';
                        $data->color = 'danger';
                    }
                }
                if ($data->jenis == 'utama') {
                    $data->jenis_kinerja = 'A. Kinerja Utama';
                } else {
                    $data->jenis_kinerja = 'B. Kinerja Tambahan';
                }
                $result[$index] = $data;
            }
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

    public function list_realisasi_skp_kepala()
    {
        $result = [];
        $status_review = '';
        $skp = skp::with('aspek_skp', 'reviewRealisasiSkp')->where('id_pegawai', Auth::user()->id_pegawai)->get();
        foreach ($skp as $key => $value) {
            $getReview = $value['reviewRealisasiSkp']->pluck('kesesuaian')->toArray();

            if (in_array("tidak", $getReview) == true && in_array("ya", $getReview) == true) {
                $status_review = 'Belum Sesuai';
                $keterangan = 'Penilai tidak menyetujui, karena tidak sesuai dengan realisasi';
                $color = 'warning';
            } else if (in_array("ya", $getReview) == true && in_array("tidak", $getReview) == false) {
                $status_review = 'Selesai';
                $keterangan = 'Penilai telah menyetujui';
                $color = 'success';
            } else {
                $status_review = 'Belum Review';
                $keterangan = 'Penilai belum melakukan review';
                $color = 'danger';
            }

            $skp[$key]['status_review'] = $status_review;
            $skp[$key]['label'] = $keterangan;
            $skp[$key]['color'] = $color;
        }

        if ($skp) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $skp
            ]);
        } else {
            return response()->json([
                'message' => 'empty data',
                'status' => false,
                'data' => $skp
            ]);
        }
    }

    public function list_realisasi_skp_pegawai()
    {


        $result = [];
        $groupSkpAtasan = [];
        $skpUtama = [];
        $skpChild = '';
        $jabatanByPegawai = DB::table('tb_jabatan')->where('id_pegawai', Auth::user()->id_pegawai)->first();
        $get_skp_atasan = DB::table('tb_skp')->select('id_skp_atasan')->where('id_pegawai', Auth::user()->id_pegawai)->groupBy('tb_skp.id_skp_atasan')->where('jenis', 'utama')->get();

        foreach ($get_skp_atasan as $key => $value) {
            $getRencanaKerjaAtasan = [];
            if (!is_null($jabatanByPegawai->parent_id)) {
                if (!is_null($value->id_skp_atasan)) {
                    $getSkpAtasan = DB::table('tb_skp')->select('id', 'rencana_kerja', 'jenis')->where('id', $value->id_skp_atasan)->where('jenis', 'utama')->first();
                    $getRencanaKerjaAtasan = [
                        'id' => $getSkpAtasan->id,
                        'rencana_kerja' => $getSkpAtasan->rencana_kerja
                    ];
                }
            } else {
                $getKegiatan = DB::table('tb_kegiatan')->select('id', 'nama_kegiatan', 'kode_kegiatan')->where('id', $value->id_skp_atasan)->first();

                if (isset($getKegiatan)) {
                    $getRencanaKerjaAtasan = [
                        'id' => $getKegiatan->id,
                        'rencana_kerja' => $getKegiatan->nama_kegiatan
                    ];
                } else {
                    $getRencanaKerjaAtasan = [];
                }
            }

            // $tes[] = $getRencanaKerjaAtasan;
            $keterangan = '';
            if ($getRencanaKerjaAtasan != []) {
                $skpUtama = skp::with('aspek_skp')->where('id_skp_atasan', $getRencanaKerjaAtasan['id'])->where('jenis', 'utama')->where('id_pegawai', Auth::user()->id_pegawai)->get();

                foreach ($skpUtama as $keys => $values) {
                    $getReview = $values['reviewRealisasiSkp']->pluck('kesesuaian')->toArray();

                    if (in_array("tidak", $getReview) == true && in_array("ya", $getReview) == true) {
                        $status_review = 'Belum Sesuai';
                        $keterangan = 'Penilai tidak menyetujui, karena tidak sesuai dengan realisasi';
                        $color = 'warning';
                    } else if (in_array("ya", $getReview) == true && in_array("tidak", $getReview) == false) {
                        $status_review = 'Selesai';
                        $keterangan = 'Penilai telah menyetujui';
                        $color = 'success';
                    } else {
                        $status_review = 'Belum Review';
                        $keterangan = 'Penilai belum melakukan review';
                        $color = 'danger';
                    }

                    $skpUtama[$keys]['status_review'] = $status_review;
                    $skpUtama[$keys]['label'] = $keterangan;
                    $skpUtama[$keys]['color'] = $color;
                }
            }

            if (count($getRencanaKerjaAtasan) > 0) {
                $result['utama'][$key]['atasan'] = $getRencanaKerjaAtasan;
                $result['utama'][$key]['skp'] = $skpUtama;
            }
        }

        $skp_tambahan = skp::with('aspek_skp')->where('jenis', 'tambahan')->where('id_pegawai', Auth::user()->id_pegawai)->get();

        foreach ($skp_tambahan as $keys => $values) {
            $getReview = $values['reviewRealisasiSkp']->pluck('kesesuaian')->toArray();

            if (in_array("tidak", $getReview) == true && in_array("ya", $getReview) == true) {
                $status_review = 'Belum Sesuai';
                $keterangan = 'Penilai tidak menyetujui, karena tidak sesuai dengan realisasi';
                $color = 'warning';
            } else if (in_array("ya", $getReview) == true && in_array("tidak", $getReview) == false) {
                $status_review = 'Selesai';
                $keterangan = 'Penilai telah menyetujui';
                $color = 'success';
            } else {
                $status_review = 'Belum Review';
                $keterangan = 'Penilai belum melakukan review';
                $color = 'danger';
            }

            $skp_tambahan[$keys]['status_review'] = $status_review;
            $skp_tambahan[$keys]['label'] = $keterangan;
            $skp_tambahan[$keys]['color'] = $color;
        }

        if (count($skp_tambahan) > 0) {
            $result['tambahan'] = $skp_tambahan;
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

    public function store(Request $request)
    {
        // return $request->all();
        $validator = Validator::make($request->all(), [
            'id_aspek_skp' => 'required|array',
            'realisasi_bulanan' => 'required|array',
            'bulan' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $data = '';
        $tes = [];
        if ($request->bulan != 0) {
            for ($i = 0; $i < count($request->id_aspek_skp); $i++) {
                $data = realisasi_skp::where('id_aspek_skp', $request->id_aspek_skp[$i])->where('bulan', $request->bulan)->first();
                $data->realisasi_bulanan = $request->realisasi_bulanan[$i];
                $data->save();
            }
        } else {
            for ($i = 0; $i < count($request->id_aspek_skp); $i++) {

                for ($y = 0; $y < count($request->realisasi_bulanan[$i]); $y++) {
                    $data = realisasi_skp::where('id_aspek_skp', $request->id_aspek_skp[$i])->where('bulan', $y + 1)->first();
                    $data->realisasi_bulanan = $request->realisasi_bulanan[$i][$y];
                    $data->save();
                }
            }
        }

        // return $data;

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
        $data = realisasi_skp::where('id', $params)->first();

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
            'id_aspek_skp' => 'required|numeric',
            'realisasi_bulanan' => 'required|numeric',
            'bulan' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $data = realisasi_skp::where('id', $params)->first();
        $data->id_aspek_skp = $request->id_aspek_skp;
        $data->realisasi_bulanan = $request->realisasi_bulanan;
        $data->bulan = $request->bulan;
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
        $data = realisasi_skp::where('id', $params)->first();
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

    public function realisasiKuantitas($params, $id_skp)
    {
        $num = 0;
        $aktivitas = DB::table('tb_aktivitas')->whereMonth('tanggal', '=', $params)->where('id_pegawai', Auth::user()->id_pegawai)->where('id_skp', $id_skp)->get();
        // return $aktivitas;

        if (count($aktivitas) > 0) {
            foreach ($aktivitas as $key => $value) {
                $num += $value->hasil;
            }
        }

        return response()->json([
            'message' => 'Success',
            'status' => true,
            'data' => $num
        ]);
    }
}
