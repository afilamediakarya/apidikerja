<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\review_realisasi_skp;
use App\Models\skp;
use App\Models\atasan;
use Auth;
use DB;
use Validator;

class reviewRealisasiSkpController extends Controller
{

    public function list()
    {
        $jabatanPegawai = DB::table('tb_jabatan')->select('id')->where('id_pegawai', Auth::user()->id_pegawai)->first();
        $myArray = [];
        $groupId = [];
        $groupSkpPegawai = [];
        if (isset($jabatanPegawai)) {
            $myArray = DB::table('tb_jabatan')->select('tb_jabatan.id', 'tb_jabatan.id_pegawai', 'tb_jabatan.nama_jabatan', 'tb_pegawai.nama', 'tb_pegawai.nip')->join('tb_pegawai', 'tb_jabatan.id_pegawai', '=', 'tb_pegawai.id')->where('parent_id', $jabatanPegawai->id)->get();
            foreach ($myArray as $key => $value) {
                $skp = skp::select('id')->where('tb_skp.id_jabatan', $value->id)->where('tahun', request('tahun'))->get();
                $filter_ = array();
                foreach ($skp as $a => $b) {
                    foreach ($b['reviewRealisasiSkp'] as $x => $y) {
                        array_push($filter_, $y->kesesuaian);
                    }
                }

                // return $label;

                if (in_array("tidak", $filter_) == true && in_array("ya", $filter_) == true) {
                    $status = 'Belum Sesuai';
                } else if (in_array("ya", $filter_) == true && in_array("tidak", $filter_) == false) {
                    $status = 'Selesai';
                } else {
                    $status = 'Belum Review';
                }
                $value->status = $status;
            }
        }

        if ($myArray) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $myArray
            ]);
        } else {
            return response()->json([
                'message' => 'Failed',
                'status' => false,
                'data' => $myArray
            ]);
        }
    }

    public function skpbyId($params)
    {
        $type = request('type');
        $tahun = request('tahun');
        $bulan = request('bulan');

        $jabatanByPegawai =  DB::table('tb_jabatan')->select('tb_jabatan.id', 'tb_jabatan.id_pegawai')->join('tb_pegawai', 'tb_jabatan.id_pegawai', '=', 'tb_pegawai.id')->where('tb_jabatan.id_pegawai', $params)->first();

        // $result = skp::select('tb_skp.id', 'tb_skp.id_jabatan', 'tb_skp.id_skp_atasan', 'tb_skp.jenis', 'tb_skp.rencana_kerja', 'tb_skp.tahun')->with('aspek_skp', 'reviewRealisasiSkp')->where('tahun', $tahun)->where('id_jabatan', $jabatanByPegawai->id)->orderBy('jenis', 'ASC')->get();

        $result = DB::table('tb_skp')
            ->select('tb_skp.*', 'tb_aspek_skp.iki', 'tb_aspek_skp.aspek_skp', 'tb_aspek_skp.satuan', 'tb_target_skp.target', 'tb_realisasi_skp.realisasi_bulanan', 'tb_review_realisasi_skp.kesesuaian', 'tb_review_realisasi_skp.keterangan')
            ->join('tb_aspek_skp', 'tb_aspek_skp.id_skp', 'tb_skp.id')
            ->join('tb_target_skp', 'tb_target_skp.id_aspek_skp', 'tb_aspek_skp.id')
            ->join('tb_realisasi_skp', 'tb_realisasi_skp.id_aspek_skp', 'tb_aspek_skp.id')
            ->join('tb_review_realisasi_skp', 'tb_review_realisasi_skp.id_skp', 'tb_skp.id')
            ->where('tb_skp.tahun', $tahun)
            ->where('id_jabatan', $jabatanByPegawai->id)
            ->where('tb_realisasi_skp.bulan', $bulan)
            ->where('tb_review_realisasi_skp.bulan', '' . $bulan . '')
            ->where('tb_target_skp.bulan', $bulan)
            ->groupBy('tb_aspek_skp.id')
            ->orderBy('tb_skp.jenis', 'ASC')
            ->get();

        foreach ($result as $key => $value) {
            if (!is_null($value->id_skp_atasan)) {
                $value->skp_atasan = DB::table('tb_skp')->where('id', $value->id_skp_atasan)->first()->rencana_kerja;
            } else {
                $value->skp_atasan = '-';
            }

            if ($value->jenis == 'utama') {
                $value->jenis_kinerja = 'A. Kinerja Utama';
            } else {
                $value->jenis_kinerja = 'B. Kinerja Tambahan';
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
        //       $result = [];
        //      $groupSkpAtasan = [];
        //      $tes = [];
        //     $get_skp_atasan = DB::table('tb_skp')->select('id_skp_atasan')->where('id_pegawai',$params)->groupBy('tb_skp.id_skp_atasan')->get();

        // // return $get_skp_atasan;

        // if (!is_null($get_skp_atasan)) {
        //        foreach ($get_skp_atasan as $key => $value) {
        //             $getSkpByAtasan = DB::table('tb_skp')->select('id','rencana_kerja','jenis')->where('id',$value->id_skp_atasan)->first();
        //             if (!is_null($getSkpByAtasan)) {
        //                 $skpChild = skp::with('aspek_skp')->where('id_skp_atasan',$getSkpByAtasan->id)->where('id_pegawai',$params)->get();
        //                 foreach ($skpChild as $xx => $vv) {
        //                     $realisasi_bulan = DB::table('tb_review_realisasi_skp')->where('id_skp',$vv->id)->where('bulan',$bulan)->first();   
        //                     $result['utama'][$key]['atasan'] = $getSkpByAtasan;
        //                     $result['utama'][$key]['skp_child'] = $skpChild;
        //                     $result['utama'][$key]['skp_child'][$xx]['realisasi_bulan'] = $realisasi_bulan;
        //                 }
        //             }


        //         }  
        // }    

        // $skp_tambahan = skp::with('aspek_skp')->where('jenis','tambahan')->where('id_pegawai',$params)->get();

        // if (count($skp_tambahan) > 0) {
        //     foreach ($skp_tambahan as $yy => $vals) {
        //         $realisasi_bulan = DB::table('tb_review_realisasi_skp')->where('id_skp',$vals->id)->where('bulan',$bulan)->first();   
        //         $result['tambahan'] = $skp_tambahan;
        //         $result['tambahan'][$yy]['realisasi_bulan'] = $realisasi_bulan;
        //     }
        // }


        // if ($result) {
        //     return response()->json([
        //         'message' => 'Success',
        //         'status' => true,
        //         'data' => $result
        //     ]);
        // }else{
        //     return response()->json([
        //         'message' => 'empty data',
        //         'status' => false,
        //          'data' => $result
        //     ]);
        // }
    }

    public function store(Request $request)
    {
        // return $request->all();
        $validator = Validator::make($request->all(), [
            'id_skp' => 'required|array',
            'keterangan' => 'required|array',
            'kesesuaian' => 'required|array',
            'bulan' => 'required|array',
        ]);



        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        // for ($i = 0; $i < count($request->id_skp); $i++) {
        //     $data = review_realisasi_skp::where('id_skp', $request->id_skp[$i])->where('bulan', $request->bulan[$i])->first();
        //     $data->keterangan = $request->keterangan[$i];
        //     $data->kesesuaian = $request->kesesuaian[$i];
        //     $data->save();
        // }

        $id = '';
        for ($i = 0; $i < count($request->id_skp); $i++) {
            $data = review_realisasi_skp::where('id_skp', $request->id_skp[$i])->where('bulan', $request->bulan[$i])->first();

            if ($id != $data->id_skp) {
                $id = $data->id_skp;
                $data->keterangan = $request['keterangan'][$i];
                $data->kesesuaian = $request['kesesuaian'][$i];
                // return $data;
                $data->save();
            }
        }

        // return $cek;


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
}
