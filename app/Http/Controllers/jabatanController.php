<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\jabatan;
use App\Models\pegawai;
use App\Models\jenis_jabatan;
use Auth;
use DB;
use Illuminate\Validation\Rule;

class jabatanController extends Controller
{
    public function list(Request $request)
    {
        $data = '';
        if (Auth::user()->role == 'super_admin') {
            $data = DB::table('tb_pegawai')
                ->select('tb_jabatan.id', 'tb_jabatan.nama_jabatan', 'tb_jabatan.parent_id', 'tb_pegawai.nama', 'tb_jenis_jabatan.level', 'tb_satuan_kerja.nama_satuan_kerja', 'tb_jabatan.id_satuan_kerja')
                ->rightJoin('tb_jabatan', 'tb_pegawai.id', '=', 'tb_jabatan.id_pegawai')
                ->join('tb_satuan_kerja', 'tb_jabatan.id_satuan_kerja', '=', 'tb_satuan_kerja.id')
                ->join('tb_jenis_jabatan', 'tb_jenis_jabatan.id', '=', 'tb_jabatan.id_jenis_jabatan')
                ->where('tb_jabatan.id_satuan_kerja', request('dinas'))
                ->orderBy('tb_jenis_jabatan.level', 'ASC')
                ->get();
        } else {
            $pegawai = pegawai::select('id_satuan_kerja')->where('id', Auth::user()->id_pegawai)->first();
            $data = DB::table('tb_pegawai')
                ->select('tb_jabatan.id', 'tb_jabatan.nama_jabatan', 'tb_jabatan.parent_id', 'tb_pegawai.nama', 'tb_jenis_jabatan.level', 'tb_satuan_kerja.nama_satuan_kerja', 'tb_jabatan.id_satuan_kerja')
                ->rightJoin('tb_jabatan', 'tb_pegawai.id', '=', 'tb_jabatan.id_pegawai')
                ->join('tb_satuan_kerja', 'tb_jabatan.id_satuan_kerja', '=', 'tb_satuan_kerja.id')
                ->join('tb_jenis_jabatan', 'tb_jenis_jabatan.id', '=', 'tb_jabatan.id_jenis_jabatan')
                ->where('tb_jabatan.id_satuan_kerja', $pegawai->id_satuan_kerja)
                ->orderBy('tb_jenis_jabatan.level', 'ASC')->get();
        }

        foreach ($data as $key => $value) {
            $getAtasan = DB::table('tb_jabatan')
                ->select('tb_jabatan.id', 'tb_jabatan.nama_jabatan', 'tb_pegawai.nama', 'tb_satuan_kerja.nama_satuan_kerja', 'tb_jabatan.id_satuan_kerja')
                ->join('tb_satuan_kerja', 'tb_jabatan.id_satuan_kerja', '=', 'tb_satuan_kerja.id')
                ->rightJoin('tb_pegawai', 'tb_jabatan.id_pegawai', '=', 'tb_pegawai.id')
                ->where('tb_jabatan.id', $value->parent_id)
                ->get();

            $value->atasan_langsung = $getAtasan;
        }
        if ($data) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $data
            ]);
        } else {
            return response()->json([
                'message' => 'Failed',
                'status' => false,
                'data' => $data
            ]);
        }
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'id_satuan_kerja' => 'required|numeric',
            'id_jenis_jabatan' => 'required|numeric',
            'parent_id' => Rule::requiredIf($request->level > 1),
            'pembayaran_tpp' => 'required',
            'nama_jabatan' => 'required',
            'status_jabatan' => 'required',
            'id_lokasi' => 'required',
            'kelompok_jabatan' => 'required',
             'kelas_jabatan' => 'required',
            'target_waktu' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(),422);
        }

        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/image', $filename);
        }

        $data = new jabatan();
        $data->id_satuan_kerja = $request->id_satuan_kerja;
        $data->id_jenis_jabatan = $request->id_jenis_jabatan;
        $data->id_pegawai = $request->id_pegawai;
        $data->parent_id = $request->parent_id;
        $data->nama_jabatan = $request->nama_jabatan;
        $data->status_jabatan = $request->status_jabatan;
        $data->pembayaran_tpp = $request->pembayaran_tpp;
        $data->id_lokasi = $request->id_lokasi;
        $data->id_kelompok_jabatan = $request->kelompok_jabatan;
        $data->nilai_jabatan = str_replace(',', '', $request->nilai_jabatan);
        $data->kelas_jabatan = $request->kelas_jabatan;
        $data->target_waktu = $request->target_waktu;

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

    public function show($params)
    {
        $result = [];
        $parent_ = '';
        $data = jabatan::where('id', $params)->first();
        $parent = jabatan::where('id', $data->parent_id)->first();

        if (isset($parent)) {
            $parent_ = $parent->id;
        } else {
            $parent_ = $parent;
        }

        $result = [
            'id' => $data->id,
            'id_satuan_kerja' => $data->id_satuan_kerja,
            'id_lokasi' => $data->id_lokasi,
            'nama_jabatan' => $data->nama_jabatan,
            'nilai_jabatan' =>  $data->nilai_jabatan,
            'status_jabatan' => $data->status_jabatan,
            'pegawai' => $data->pegawai,
            'pembayaran_tpp' => $data->pembayaran_tpp,
            'target_waktu' => $data->target_waktu,
        'nested_jabatan' => [
                'id_jenis_jabatan' => $data->id_jenis_jabatan,
                'kelompok_jabatan' => $data->id_kelompok_jabatan,
            ],
            'kelas_jabatan' => $data->kelas_jabatan,
            'parent_id' => [
                'parent_id' => $parent_,
                'jenis_jabatan' => $data->id_jenis_jabatan,
            ],

        ];


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

    public function update($params, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_satuan_kerja' => 'required|numeric',
            'id_jenis_jabatan' => 'required|numeric',
            'parent_id' => Rule::requiredIf($request->level > 1),
            'pembayaran_tpp' => 'required',
            'nama_jabatan' => 'required',
            'status_jabatan' => 'required',
            'id_lokasi' => 'required',
            'kelompok_jabatan' => 'required',
            'kelas_jabatan' => 'required',
            'target_waktu' => 'required'

        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $data = jabatan::where('id', $params)->first();
        $data->id_satuan_kerja = $request->id_satuan_kerja;
        $data->id_jenis_jabatan = $request->id_jenis_jabatan;
        $data->id_pegawai = $request->id_pegawai;
        $data->parent_id = $request->parent_id;
        $data->nama_jabatan = $request->nama_jabatan;
        $data->status_jabatan = $request->status_jabatan;
        $data->pembayaran_tpp = $request->pembayaran_tpp;
        $data->id_lokasi = $request->id_lokasi;
        $data->id_kelompok_jabatan = $request->kelompok_jabatan;
        $data->nilai_jabatan = str_replace(',', '', $request->nilai_jabatan);
        $data->kelas_jabatan = $request->kelas_jabatan;
        $data->target_waktu = $request->target_waktu;
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
        $data = jabatan::where('id', $params)->first();
        $data->delete();

        if ($data) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $data,
            ]);
        } else {
            return response()->json([
                'message' => 'Failed',
                'status' => false
            ]);
        }
    }

    public function jabatanAtasan($level, $id_satuan_kerja)
    {
        $current_user = pegawai::where('id', Auth::user()->id_pegawai)->first();
        $result = [];
        // return $current_user['id_satuan_kerja'];
        $data = jabatan::where('id_satuan_kerja', $id_satuan_kerja)->where('level', $level - 1)->get();

        foreach ($data as $key => $value) {
            $result[$key] = [
                'id' => $value->id,
                'value' => $value->nama_jabatan
            ];
        }

        return response()->json($result);
    }

    public function getPegawaiBySatuanKerja()
    {
        $current_user = pegawai::where('id', Auth::user()->id_pegawai)->first();
        // return $current_user;

        $data = DB::table('tb_pegawai')
            ->select('tb_pegawai.id', 'tb_pegawai.nama')
            ->where('tb_pegawai.id_satuan_kerja', $current_user['id_satuan_kerja'])
            ->join('tb_jabatan', 'tb_pegawai.id', '=', 'tb_jabatan.id_pegawai')
            ->orderBy('tb_jabatan.id_jenis_jabatan', 'ASC')
            ->get();

        foreach ($data as $key => $value) {
            $result[] = [
                'id' => $value->id,
                'value' => $value->nama
            ];
        }

        return response()->json($result);
    }

    public function getOptionJenisJabatan()
    {
        $data = jenis_jabatan::orderBy('id', 'DESC')->get();

        foreach ($data as $key => $value) {
            $result[$key] = [
                'id' => $value->id,
                'value' => $value->jenis_jabatan
            ];
        }

        return response()->json($result);
    }

    public function getParent($params)
    {
        $result = [];
        $getParent = '';
        $satuan_kerja = '';
    // satuan_kerja
        $current_jenis_jabatan = jenis_jabatan::where('id', $params)->first();

        $current_user = pegawai::where('id', Auth::user()->id_pegawai)->first();

        isset($current_user) ? $satuan_kerja = $current_user['id_satuan_kerja'] : $satuan_kerja = request('satuan_kerja');
        if ($params == 2 || $params == 3) {
            $getParent = DB::table('tb_jabatan')->select('tb_jabatan.nama_jabatan', 'tb_pegawai.nama', 'tb_jabatan.id_pegawai', 'tb_jabatan.id')->join('tb_pegawai', 'tb_pegawai.id', '=', 'tb_jabatan.id_pegawai')->join('tb_jenis_jabatan', 'tb_jenis_jabatan.id', '=', 'tb_jabatan.id_jenis_jabatan')->where('tb_jenis_jabatan.level', '=', 1)->get();
        } else {
            $getParent = DB::table('tb_jabatan')->select('tb_jabatan.nama_jabatan', 'tb_pegawai.nama', 'tb_jabatan.id_pegawai', 'tb_jabatan.id')->join('tb_pegawai', 'tb_pegawai.id', '=', 'tb_jabatan.id_pegawai')->join('tb_jenis_jabatan', 'tb_jenis_jabatan.id', '=', 'tb_jabatan.id_jenis_jabatan')->where('tb_jenis_jabatan.level', '<', $current_jenis_jabatan['level'])->where('tb_jabatan.id_satuan_kerja', $satuan_kerja)->get();
        }


        // return $getParent;

        foreach ($getParent as $key => $value) {
            // if ($value->id_pegawai != $current_user->id) {
            $result[] = [
                'id' => $value->id,
                'value' => $value->nama . ' - ' . $value->nama_jabatan
            ];
            // }   
        }

        return response()->json($result);
    }
}
