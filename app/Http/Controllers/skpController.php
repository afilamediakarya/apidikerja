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

class skpController extends Controller
{
    public function list($params)
    {

        $tahun =  request('tahun', date('Y'));
        $type =  request('type');
        $result = array();

        $jabatanByPegawai =  DB::table('tb_jabatan')->select('tb_jabatan.id', 'tb_jabatan.id_pegawai')->join('tb_pegawai', 'tb_jabatan.id_pegawai', '=', 'tb_pegawai.id')->where('tb_jabatan.id_pegawai', Auth::user()->id_pegawai)->first();

        if ($type == 'tahunan') {
            if ($params == 'pegawai') {

                // $result = skp::with('aspek_skp')->where('tahun', $tahun)->where('id_jabatan', $jabatanByPegawai->id)->orderBy('jenis', 'ASC')->get();

                $result = DB::table('tb_skp')
                    ->join('tb_aspek_skp', 'tb_aspek_skp.id_skp', 'tb_skp.id')
                    ->join('tb_target_skp', 'tb_target_skp.id_aspek_skp', 'tb_aspek_skp.id')
                    ->where('tb_skp.tahun', $tahun)
                    ->where('id_jabatan', $jabatanByPegawai->id)
                    ->groupBy('tb_aspek_skp.id')
                    ->orderBy('tb_skp.jenis', 'ASC')
                    ->orderBy('tb_skp.id', 'ASC')
                    ->get();

                // return $result;

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
            } else {

                // $result = skp::with('aspek_skp')->where('tahun', $tahun)->where('id_jabatan', $jabatanByPegawai->id)->orderBy('jenis', 'ASC')->get();

                $result = DB::table('tb_skp')->join('tb_aspek_skp', 'tb_aspek_skp.id_skp', 'tb_skp.id')->join('tb_target_skp', 'tb_target_skp.id_aspek_skp', 'tb_aspek_skp.id')->where('tb_skp.tahun', $tahun)->where('id_jabatan', $jabatanByPegawai->id)->groupBy('tb_aspek_skp.id')->orderBy('tb_skp.jenis', 'ASC')->get();

                foreach ($result as $key => $value) {
                    if ($value->jenis == 'utama') {
                        $value->jenis_kinerja = 'A. Kinerja Utama';
                    } else {
                        $value->jenis_kinerja = 'B. Kinerja Tambahan';
                    }
                }
            }
        } else {
            $skp_filter =  DB::table('tb_skp')
                ->select('tb_skp.id', 'tb_skp.id_skp_atasan')
                ->join('tb_aspek_skp', 'tb_aspek_skp.id_skp', 'tb_skp.id')
                ->join('tb_target_skp', 'tb_target_skp.id_aspek_skp', 'tb_aspek_skp.id')
                ->where('tb_skp.tahun', $tahun)
                ->where('id_jabatan', $jabatanByPegawai->id)
                ->where('tb_target_skp.bulan', request('bulan'))
                ->orderBy('tb_skp.jenis', 'ASC')
                ->orderBy('tb_skp.id', 'ASC')
                ->get();

            // $skp_filter =  DB::table('tb_skp')->select('tb_skp.id', 'tb_skp.id_skp_atasan')->join('tb_aspek_skp', 'tb_aspek_skp.id_skp', 'tb_skp.id')->join('tb_target_skp', 'tb_target_skp.id_aspek_skp', 'tb_aspek_skp.id')->groupBy('tb_skp.id')->where('tb_skp.tahun', $tahun)->where('id_jabatan', $jabatanByPegawai->id)->where('tb_target_skp.bulan', request('bulan'))->get();

            // return $skp_filter;
            if ($params == 'pegawai') {
                foreach ($skp_filter as $index => $val) {
                    $data = skp::with('aspek_skp')->where('id', $val->id)->orderBy('jenis', 'ASC')->first();
                    if ($data->jenis == 'utama') {
                        $data->jenis_kinerja = 'A. Kinerja Utama';
                        $data->skp_atasan = DB::table('tb_skp')->where('id', $val->id_skp_atasan)->first()->rencana_kerja;
                    } else {
                        $data->jenis_kinerja = 'B. Kinerja Tambahan';
                        $data->skp_atasan = '-';
                    }

                    $result[$index] = $data;
                }
            } else {
                foreach ($skp_filter as $index => $val) {
                    $data = skp::with('aspek_skp')->where('id', $val->id)->orderBy('id', 'DESC')->first();
                    if ($data->jenis == 'utama') {
                        $data->jenis_kinerja = 'A. Kinerja Utama';
                    } else {
                        $data->jenis_kinerja = 'B. Kinerja Tambahan';
                    }
                    $result[$index] = $data;
                }
            }
        }
        // return $result;



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
    public function list_skp_kepala()
    {
        $result = [];
        $skpUtama = skp::with('aspek_skp')->where('jenis', 'utama')->where('id_pegawai', Auth::user()->id_pegawai)->get();
        $skpTambahan = skp::with('aspek_skp')->where('jenis', 'tambahan')->where('id_pegawai', Auth::user()->id_pegawai)->get();

        $result = [
            'utama' => $skpUtama,
            'tambahan' => $skpTambahan
        ];

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

    public function list_skp_pegawai()
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


            if ($getRencanaKerjaAtasan != []) {
                $skpUtama = skp::with('aspek_skp')->where('id_skp_atasan', $getRencanaKerjaAtasan['id'])->where('jenis', 'utama')->where('id_pegawai', Auth::user()->id_pegawai)->get();
            }

            if (count($skpUtama) > 0) {
                $result['utama'][$key]['atasan'] = $getRencanaKerjaAtasan;
                $result['utama'][$key]['skp'] = $skpUtama;
            }
        }

        $skp_tambahan = skp::with('aspek_skp')->where('jenis', 'tambahan')->where('id_pegawai', Auth::user()->id_pegawai)->get();

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

        $data =  DB::table('tb_jabatan')->select('tb_jabatan.id', 'tb_jabatan.id_pegawai')->join('tb_pegawai', 'tb_jabatan.id_pegawai', '=', 'tb_pegawai.id')->where('tb_jabatan.id_pegawai', Auth::user()->id_pegawai)->first();

        $skp = new skp();
        $skp->id_jabatan = $data->id;
        $skp->id_satuan_kerja = $request->id_satuan_kerja;
        $skp->id_skp_atasan = $request->id_skp_atasan;
        $skp->jenis = $request->jenis;
        $skp->rencana_kerja = $request->rencana_kerja;
        $skp->tahun = $request->tahun;
        $skp->save();

        $review = new review_skp();
        $review->id_skp = $skp->id;
        $review->kesesuaian = 'tidak';
        // $review->id_pegawai = $data->id_pegawai;
        $review->save();

        $review_realisasi_skp = new review_realisasi_skp();
        $review_realisasi_skp->id_skp = $skp->id;
        $review_realisasi_skp->kesesuaian = 'tidak';
        $review_realisasi_skp->bulan = '0';
        // $review_realisasi_skp->id_pegawai = $data->id_pegawai;
        $review_realisasi_skp->save();


        foreach ($request['aspek'] as $key => $value) {
            $aspek = new aspek_skp();
            $aspek->id_skp = $skp->id;
            $aspek->aspek_skp = $value['type_aspek'];
            $aspek->iki = $value['iki'];
            $aspek->satuan = $value['satuan'];
            $aspek->save();

            $realisasi_skp = new realisasi_skp();
            $realisasi_skp->id_aspek_skp = $aspek->id;
            $realisasi_skp->realisasi_bulanan = 0;
            $realisasi_skp->bulan = '0';
            // $realisasi_skp->id_pegawai = $data->id_pegawai;
            $realisasi_skp->save();

            $target = new target_skp();
            $target->id_aspek_skp = $aspek->id;
            $target->target = $value['target'];
            $target->bulan = '0';
            // $target->id_pegawai = $data->id_pegawai;
            $target->save();
        }
        return $aspek;


        if ($skp) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $skp
            ]);
        } else {
            return response()->json([
                'message' => 'Failed',
                'status' => false
            ], 422);
        }
    }

    public function store_bulanan(Request $request)
    {

        $data =  DB::table('tb_jabatan')->select('tb_jabatan.id', 'tb_jabatan.id_pegawai')->join('tb_pegawai', 'tb_jabatan.id_pegawai', '=', 'tb_pegawai.id')->where('tb_jabatan.id_pegawai', Auth::user()->id_pegawai)->first();

        // return $data;

        $review_realisasi_skp = new review_realisasi_skp();
        $review_realisasi_skp->id_skp = $request['rencana_kerja'];
        $review_realisasi_skp->kesesuaian = 'tidak';
        $review_realisasi_skp->bulan = $request['bulan'];
        // $review_realisasi_skp->id_pegawai = $data->id_pegawai;
        $review_realisasi_skp->save();

        foreach ($request['id_aspek'] as $key => $value) {
            $target = new target_skp();
            $target->id_aspek_skp = $value;
            $target->target = $request['target'][$key];
            $target->bulan = $request['bulan'];
            // $target->id_pegawai = $data->id_pegawai;
            $target->save();

            $realisasi_skp = new realisasi_skp();
            $realisasi_skp->id_aspek_skp = $value;
            $realisasi_skp->realisasi_bulanan = 0;
            $realisasi_skp->bulan = $request['bulan'];
            // $realisasi_skp->id_pegawai = $data->id_pegawai;
            $realisasi_skp->save();
        }
        if ($target) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $target
            ]);
        } else {
            return response()->json([
                'message' => 'Failed',
                'status' => false
            ], 422);
        }
    }

    public function show($params)
    {
        // $jabatanByPegawai =  DB::table('tb_jabatan')->select('tb_jabatan.id','tb_jabatan.id_pegawai')->join('tb_pegawai','tb_jabatan.id_pegawai','=','tb_pegawai.id')->where('tb_jabatan.id_pegawai',Auth::user()->id_pegawai)->first();
        $data = skp::with('aspek_skp')->where('id', $params)->first();
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
        $jabatanByPegawai =  DB::table('tb_jabatan')->select('tb_jabatan.id', 'tb_jabatan.id_pegawai')->join('tb_pegawai', 'tb_jabatan.id_pegawai', '=', 'tb_pegawai.id')->where('tb_jabatan.id_pegawai', Auth::user()->id_pegawai)->first();

        $skp = skp::where('id', $params)->first();
        $skp->id_jabatan = $jabatanByPegawai->id;
        $skp->id_satuan_kerja = $request->id_satuan_kerja;
        $skp->id_skp_atasan = $request->id_skp_atasan;
        $skp->jenis = $request->jenis;
        $skp->rencana_kerja = $request->rencana_kerja;
        $skp->tahun = $request->tahun;
        $skp->save();

        foreach ($request['aspek'] as $key => $value) {
            $aspek = aspek_skp::where('id', $value['id'])->first();
            $aspek->id_skp = $skp->id;
            $aspek->aspek_skp = $value['type_aspek'];
            $aspek->iki = $value['iki'];
            $aspek->satuan = $value['satuan'];
            $aspek->save();

            $target = target_skp::where('id', $value['id_target'])->first();
            $target->id_aspek_skp = $aspek->id;
            $target->target = $value['target'];
            $target->bulan = '0';
            $target->save();
        }

        if (count($request->aspek_additional) > 0) {
            foreach ($request['aspek_additional'] as $index => $val) {
                $aspek_add = new aspek_skp();
                $aspek_add->id_skp = $skp->id;
                $aspek_add->aspek_skp = $val['type_aspek'];
                $aspek_add->iki = $val['iki'];
                $aspek_add->satuan = $val['satuan'];
                $aspek_add->save();

                $target_add = new target_skp();
                $target_add->id_aspek_skp = $aspek_add->id;
                $target_add->target = $val['target'];
                $target_add->bulan = '0';
                $target_add->save();
            }
        }


        if ($skp) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $skp
            ]);
        } else {
            return response()->json([
                'message' => 'Failed',
                'status' => false
            ], 422);
        }

        // return $request->type_skp;
        // if($request->type_skp == 'kepala'){
        //     return $this->update_skp_kepala($params,$request);
        // }else{
        //     return $this->update_skp_pegawai($params,$request);
        // }

    }

    public function update_bulanan($params, Request $request)
    {
        foreach ($request['id_aspek'] as $key => $value) {
            $target = target_skp::where('id', $request['id_target'][$key])->first();
            $target->id_aspek_skp = $value;
            $target->target = $request['target'][$key];
            $target->bulan = $request['bulan'];
            $target->save();
        }
        if ($target) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $target
            ]);
        } else {
            return response()->json([
                'message' => 'Failed',
                'status' => false
            ], 422);
        }
    }

    public function checkSkpAtasan($params)
    {
        // return $params;
        $status = '';
        $data = DB::table('tb_skp')->where('id_skp_atasan', $params)->get();

        return count($data);
    }

    public function checkSkpBulanan($params)
    {
        $data = DB::table('tb_aspek_skp')->where('id_skp', $params)->get();
        $result = [];

        foreach ($data as $key => $value) {
            $target_skp = DB::table('tb_target_skp')
                ->where([
                    ['id_aspek_skp', $value->id],
                    ['bulan', '!=', '0']
                ])->first();
            if ($target_skp != null) {
                $result['target'][$key] = $target_skp;
            }

            $realisasi_skp = DB::table('tb_realisasi_skp')
                ->where([
                    ['id_aspek_skp', $value->id],
                    ['bulan', '!=', '0']
                ])->first();
            if ($realisasi_skp != null) {
                $result['realisasi'][$key] = $realisasi_skp;
            }
        }

        return count($result);
    }

    public function update_skp_kepala($params, $request)
    {

        $validator = Validator::make($request->all(), [
            'id_satuan_kerja' => 'required|numeric',
            'jenis_kinerja' => 'required',
            'rencana_kerja' => 'required',
            'tahun' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $skp = new skp();
        $skp->id_pegawai = Auth::user()->id_pegawai;
        $skp->id_satuan_kerja = $request->id_satuan_kerja;
        $skp->jenis = $request->jenis_kinerja;
        $skp->rencana_kerja = $request->rencana_kerja;
        $skp->tahun = $request->tahun;
        $skp->save();

        for ($i = 0; $i < 13; $i++) {
            $review_realisasi_skp = new review_realisasi_skp();
            $review_realisasi_skp->id_skp = $skp->id;
            $review_realisasi_skp->kesesuaian = 'tidak';
            $review_realisasi_skp->bulan = $i + 1;
            $review_realisasi_skp->save();
        }

        for ($i = 0; $i < count($request->indikator_kerja_individu); $i++) {
            $aspek = new aspek_skp();
            $aspek->id_skp = $skp->id;
            $aspek->aspek_skp = "iki";
            $aspek->iki = $request->indikator_kerja_individu[$i];
            $aspek->satuan = $request->satuan[$i];
            $aspek->save();

            for ($x = 0; $x < 12; $x++) {
                $realisasi_skp = new realisasi_skp();
                $realisasi_skp->id_aspek_skp = $aspek->id;
                $realisasi_skp->realisasi_bulanan = 0;
                $realisasi_skp->bulan = $x + 1;
                $realisasi_skp->save();
            }

            for ($x = 0; $x < count($request->target_[$i]); $x++) {
                $target = new target_skp();
                $target->id_aspek_skp = $aspek->id;
                $target->target = $request->target_[$i][$x];
                $target->bulan = $x + 1;
                $target->save();
            }
        }

        $check = $this->checkSkpAtasan($params);

        if ($check > 0) {
            DB::table('tb_skp')
                ->where('id_skp_atasan', $params)
                ->update(['id_skp_atasan' => $skp->id]);
        }

        $clearSkp = $this->delete($params);

        if ($skp) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $skp
            ]);
        } else {
            return response()->json([
                'message' => 'Failed',
                'status' => false
            ], 422);
        }
    }


    public function update_skp_pegawai($params, $request)
    {
        // return $this->checkSkpAtasan($params); 
        $validator = Validator::make($request->all(), [
            'id_satuan_kerja' => 'required|numeric',
            'id_skp_atasan' => 'required|numeric',
            'jenis' => 'required',
            'rencana_kerja' => 'required',
            'tahun' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }


        $skp = skp::where('id', $params)->first();
        $skp->id_pegawai = Auth::user()->id_pegawai;
        $skp->id_satuan_kerja = $request->id_satuan_kerja;
        $skp->id_skp_atasan = $request->id_skp_atasan;
        $skp->jenis = $request->jenis;
        $skp->rencana_kerja = $request->rencana_kerja;
        $skp->tahun = $request->tahun;
        $skp->save();

        foreach ($request['aspek'] as $key => $value) {
            $aspek = aspek_skp::where('id', $value['id'])->first();
            $aspek->id_skp = $skp->id;
            $aspek->aspek_skp = $value['type_aspek'];
            $aspek->iki = $value['iki'];
            $aspek->satuan = $value['satuan'];
            $aspek->save();
            foreach ($value['target'] as $index => $res) {
                $target = target_skp::where('id', $value['id_target'][$index])->first();
                $target->id_aspek_skp = $aspek->id;
                $target->target = $res;
                $target->bulan = $index + 1;
                $target->save();
            }
        }




        // if ($clearSkp) {
        // $skp = new skp();
        // $skp->id_pegawai = Auth::user()->id_pegawai;
        // $skp->id_satuan_kerja = $request->id_satuan_kerja;
        // $skp->id_skp_atasan = $request->id_skp_atasan;
        // $skp->jenis = $request->jenis;
        // $skp->rencana_kerja = $request->rencana_kerja;
        // $skp->tahun = $request->tahun;
        // $skp->save();

        // foreach ($request['aspek'] as $key => $value) {
        //     $aspek = new aspek_skp();
        //     $aspek->id_skp = $skp->id;
        //     $aspek->aspek_skp = $value['type_aspek'];
        //     $aspek->iki = $value['iki'];
        //     $aspek->satuan = $value['satuan'];
        //     $aspek->save();
        //     foreach ($value['target'] as $index => $res) {
        //         $target = new target_skp();
        //         $target->id_aspek_skp = $aspek->id;
        //         $target->target = $res;
        //         $target->bulan = $index+1;
        //         $target->save();
        //     }
        // }

        // $check = $this->checkSkpAtasan($params);

        // if ($check > 0) {
        //      DB::table('tb_skp')
        //     ->where('id_skp_atasan', $params)
        //     ->update(['id_skp_atasan' => $skp->id]);
        // } 

        // $clearSkp = $this->delete($params);

        if ($skp) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $skp
            ]);
        } else {
            return response()->json([
                'message' => 'Failed',
                'status' => false
            ], 422);
        }
        // }
    }

    public function delete($params)
    {
        return request('type');
        $data = skp::where('id', $params)->first();
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

    public function destroy($params)
    {
        $type = request('type');
        if ($type == 'tahunan') {
            $check = $this->checkSkpAtasan($params);
            $checkSkpBulanan = $this->checkSkpBulanan($params);
            // return $checkSkpBulanan;
            if ($check > 0 || $checkSkpBulanan > 0) {
                return response()->json([
                    'message' => 'failed',
                    'status' => false,
                ]);
            } else {
                $data = skp::where('id', $params)->first();
                $data->delete();

                return response()->json([
                    'message' => 'Success',
                    'status' => true,
                ]);
            }
        } else {
            DB::table('tb_review_realisasi_skp')->where('id_skp', $params)->where('bulan', request('bulan'))->delete();
            DB::table('tb_review')->where('id_skp', $params)->where('bulan', request('bulan'))->delete();
            $aspek = DB::table('tb_aspek_skp')->where('id_skp', $params)->get();
            foreach ($aspek as $key => $value) {
                DB::table('tb_target_skp')->where('id_aspek_skp', $value->id)->where('bulan', request('bulan'))->delete();
                DB::table('tb_realisasi_skp')->where('id_aspek_skp', $value->id)->where('bulan', request('bulan'))->delete();
            }
            return response()->json([
                'message' => 'Success',
                'status' => true,
            ]);
        }
    }

    public function satuan()
    {
        $result = [];
        $data = satuan::where('status', 'active')->latest()->get();
        foreach ($data as $key => $value) {
            $result[$key] = [
                'value' => $value->nama_satuan
            ];
        }

        return response()->json($result);
        // return collect($data)->pluck('nama_satuan')->toArray();
    }

    public function optionSkp()
    {
        $result = [];

        $jabatanByPegawai = DB::table('tb_jabatan')->select('tb_jabatan.parent_id', 'tb_jenis_jabatan.level', 'tb_jabatan.id')->join('tb_jenis_jabatan', 'tb_jabatan.id_jenis_jabatan', '=', 'tb_jenis_jabatan.id')->where('id_pegawai', Auth::user()->id_pegawai)->first();

        $pegawai = pegawai::where('id', Auth::user()->id_pegawai)->first();

        if (isset($jabatanByPegawai)) {

            if (!is_null($jabatanByPegawai->parent_id)) {

                if ($jabatanByPegawai->level !== 1) {

                    $atasan = DB::table('tb_jabatan')->where('id', $jabatanByPegawai->parent_id)->first();
                    if (isset($atasan)) {
                        $getSkp = skp::where('id_jabatan', $atasan->id)->get();
                        foreach ($getSkp as $key => $value) {
                            $result[$key] = [
                                'id' => $value->id,
                                'value' => $value->rencana_kerja
                            ];
                        }
                    }

                    return response()->json([
                        'message' => 'Success',
                        'status' => true,
                        'data' => $result
                    ]);
                } else {
                    return response()->json([
                        'message' => 'Success',
                        'status' => true,
                        'data' => $result
                    ]);
                }
            } else {

                if ($jabatanByPegawai->level == "1") {
                    $kegiatan = kegiatan::where('id_satuan_kerja', $pegawai['id_satuan_kerja'])->latest()->get();
                    foreach ($kegiatan as $key => $value) {
                        $result[$key] = [
                            'id' => $value->id,
                            'value' => $value->nama_kegiatan
                        ];
                    }

                    return response()->json([
                        'message' => 'Success',
                        'status' => true,
                        'data' => $result
                    ]);
                } else {
                    return response()->json([
                        'message' => 'Data tidak ada',
                        'status' => false,
                        'data' => $result
                    ]);
                }
            }
        } else {
            return response()->json([
                'message' => 'Data tidak ada',
                'status' => false,
                'data' => $result
            ]);
        }
    }
}
