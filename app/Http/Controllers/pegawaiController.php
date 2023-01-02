<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\pegawai;
use App\Models\absen;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Validator;
use DB;
use Auth;

class pegawaiController extends Controller
{

    public function list_tes()
    {
        return $data = pegawai::select('id', 'nama', 'nip', 'golongan')->latest()->get();
    }

    public function list()
    {
        $data = '';

        if (Auth::user()->role == 'super_admin') {
            // $data = pegawai::latest()->get();
            $data = pegawai::select('id', 'nama', 'nip', 'golongan')->latest()->get();
        } else {
            $pegawai = pegawai::where('id', Auth::user()->id_pegawai)->first();
            if (isset($pegawai)) {
                $data = pegawai::select('id', 'nama', 'nip', 'golongan')->where('id_satuan_kerja', $pegawai->id_satuan_kerja)->latest()->get();
            } else {
                $data = [];
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

    public function pegawaiBySatuanKerja($params)
    {
        // return "ok";
        // $result = [];
        $data = pegawai::select('id','nama as value')->where('id_satuan_kerja', $params)->get();

        // foreach ($data as $key => $value) {
        //     $result[] = [
        //         'id' => $value->id,
        //         'value' => $value->nama
        //     ];
        // }

        return response()->json($data);
    }

    public function listPegawaiBySatuanKerja(Request $request)
    {
        $id_satuan_kerja = request('id_satuan_kerja');
        $jenis_kelamin = request('jenis_kelamin');
        $status_pernikahan = request('status_pernikahan');
        $agama = request('agama');
        $pendidikan = request('pendidikan');
        $gol_pangkat = request('gol_pangkat');

        $where = '';

        // get all pegawai if id_satuan_kerja === 'semua'
        $where .= ($id_satuan_kerja !== 'semua') ? "`tb_pegawai`.`id_satuan_kerja` = $id_satuan_kerja " : "`tb_pegawai`.`id_satuan_kerja` != 0 ";

        if ($jenis_kelamin !== 'semua') {
            $where .= "AND `tb_pegawai`.`jenis_kelamin` = '$jenis_kelamin'";
        }

        if ($status_pernikahan !== 'semua') {
            $where .= "AND `tb_pegawai`.`status_perkawinan` = '$status_pernikahan'";
        }

        if ($agama !== 'semua') {
            $where .= "AND `tb_pegawai`.`agama` = '$agama'";
        }

        if ($pendidikan !== 'semua') {
            $where .= "AND `tb_pegawai`.`pendidikan` = '$pendidikan'";
        }

        if ($gol_pangkat !== 'semua') {
            $where .= "AND `tb_pegawai`.`golongan` = '$gol_pangkat'";
        }

        $data = '';
        $data = DB::table('tb_pegawai')->select('tb_pegawai.id AS id_pegawai', 'tb_pegawai.nama', 'tb_pegawai.nip', 'tb_pegawai.golongan', 'tb_pegawai.jenis_jabatan', 'tb_pegawai.agama', 'tb_pegawai.jenis_kelamin', 'tb_pegawai.id_satuan_kerja', 'tb_jabatan.id', 'tb_jabatan.nama_jabatan', 'tb_jabatan.id_jenis_jabatan', 'tb_satuan_kerja.id', 'tb_satuan_kerja.nama_satuan_kerja',)
            ->join('tb_satuan_kerja', 'tb_pegawai.id_satuan_kerja', '=', 'tb_satuan_kerja.id')
            ->leftJoin('tb_jabatan', 'tb_pegawai.id', '=', 'tb_jabatan.id_pegawai')
            ->whereRaw("$where")
            ->orderBy('tb_satuan_kerja.id')
            ->orderBy('tb_jabatan.id_jenis_jabatan')
            ->get();

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

    public function store(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'id_satuan_kerja' => 'required',
                'nama' => 'required|string',
                'tempat_lahir' => 'required',
                'tanggal_lahir' => 'required|date',
                // 'nip' => 'required|numeric|unique:tb_pegawai',
                'nip' => 'required|numeric|unique:tb_pegawai|unique:users,username',
                'golongan' => 'required',
                'tmt_golongan' => 'required|date',
                // 'eselon' => 'required',
                // 'tmt_pegawai' => 'required|date',
                'jenis_kelamin' => 'required',
                'agama' => 'required',
                'status_perkawinan' => 'required',
                'pendidikan' => 'required',
                'lulus_pendidikan' => 'required',
                // 'pendidikan_struktural' => 'required',
                // 'lulus_pendidikan_struktural' => 'required',
                'jurusan' => 'required',
            ],
            ['nip.unique' => 'NIP/Username sudah digunakan. Cek data pegawai/user']
        );

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }



        $data = new pegawai();
        $data->id_satuan_kerja = $request->id_satuan_kerja;
        $data->nama = $request->nama;
        $data->tempat_lahir = $request->tempat_lahir;
        $data->tanggal_lahir = $request->tanggal_lahir;
        $data->nip = $request->nip;
        $data->golongan = $request->golongan;
        $data->tmt_golongan = $request->tmt_golongan;
        // $data->eselon = $request->eselon;
        // $data->jenis_jabatan = $request->jenis_jabatan;
        // $data->tmt_pegawai = $request->tmt_pegawai;
        $data->jenis_kelamin = $request->jenis_kelamin;
        $data->agama = $request->agama;
        $data->status_perkawinan = $request->status_perkawinan;
        $data->pendidikan = $request->pendidikan;
        $data->lulus_pendidikan = $request->lulus_pendidikan;
        // $data->pendidikan_struktural = $request->pendidikan_struktural;
        // $data->lulus_pendidikan_struktural = $request->lulus_pendidikan_struktural;
        $data->jurusan = $request->jurusan;
        $data->face_character = $request->face_character;
        $data->save();

        $user = new User();
        $user->username = $data->nip;
        $user->password = Hash::make('dikerja');
        $user->id_pegawai = $data->id;
        $user->role = 'pegawai';
        $user->save();


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

        $data = pegawai::where('id', $params)->first();

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

        if ($request->type == 'pegawai') {
            $validator = Validator::make($request->all(), [
                'id_satuan_kerja' => 'required',
                'nama' => 'required|string',
                'tempat_lahir' => 'required',
                'tanggal_lahir' => 'required|date',
                'nip' => 'required|numeric',
                'golongan' => 'required',
                'tmt_golongan' => 'required',
                // 'eselon' => 'required',
                // 'tmt_pegawai' => 'required|date',
                'jenis_kelamin' => 'required',
                'agama' => 'required',
                'status_perkawinan' => 'required',
                'pendidikan' => 'required',
                'lulus_pendidikan' => 'required',
                // 'pendidikan_struktural' => 'required',
                // 'lulus_pendidikan_struktural' => 'required',
                'jurusan' => 'required',
            ]);
        } else {
            $validator = Validator::make($request->all(), [
                'id_satuan_kerja' => 'required',
                'nama' => 'required|string',
                'tempat_lahir' => 'required',
                'tanggal_lahir' => 'required|date',
                'nip' => 'required|numeric',
                'golongan' => 'required',
                'tmt_golongan' => 'required',
                // 'tmt_pegawai' => 'required|date',
                'jenis_kelamin' => 'required',
                'agama' => 'required',
                'status_perkawinan' => 'required',
                'pendidikan' => 'required',
                'lulus_pendidikan' => 'required',
                // 'pendidikan_struktural' => 'required',
                // 'lulus_pendidikan_struktural' => 'required',
                'jurusan' => 'required',
            ]);
        }




        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $data =  pegawai::where('id', $params)->first();
        $data->id_satuan_kerja = $request->id_satuan_kerja;
        $data->nama = $request->nama;
        $data->tempat_lahir = $request->tempat_lahir;
        $data->tanggal_lahir = $request->tanggal_lahir;
        $data->nip = $request->nip;
        $data->golongan = $request->golongan;
        $data->tmt_golongan = $request->tmt_golongan;
        // $data->eselon = $request->eselon;
        // $data->jenis_jabatan = $request->jenis_jabatan;
        // $data->tmt_pegawai = $request->tmt_pegawai;
        $data->jenis_kelamin = $request->jenis_kelamin;
        $data->agama = $request->agama;
        $data->status_perkawinan = $request->status_perkawinan;
        $data->pendidikan = $request->pendidikan;
        $data->lulus_pendidikan = $request->lulus_pendidikan;
        // $data->pendidikan_struktural = $request->pendidikan_struktural;
        // $data->lulus_pendidikan_struktural = $request->lulus_pendidikan_struktural;
        $data->jurusan = $request->jurusan;
        if (isset($request->face_character)) {
            $data->face_character = $request->face_character;
        }
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
        $data = pegawai::where('id', $params)->first();
        $data->delete();

        $user = User::where('id_pegawai', $params)->first();
        $user->delete();

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

    public function optionAgama()
    {
        $result = [];
        $data = DB::table('tb_agama')->orderBy('id', 'ASC')->get();
        foreach ($data as $key => $value) {
            $result[$key] = [
                'value' => $value->nama_agama
            ];
        }
        return response()->json($result);
    }

    public function optionStatusKawin()
    {
        $result = [];
        $data = DB::table('tb_status_kawin')->orderBy('id', 'ASC')->get();
        foreach ($data as $key => $value) {
            $result[$key] = [
                'value' => $value->nama_status_kawin
            ];
        }
        return response()->json($result);
    }

    public function optionGolongan()
    {
        $result = [];
        $data = DB::table('tb_golongan')->orderBy('id', 'ASC')->get();
        foreach ($data as $key => $value) {
            $result[$key] = [
                'value' => $value->nama_golongan
            ];
        }
        return response()->json($result);
    }

    public function optionStatusPegawai()
    {
        $result = [];
        $data = DB::table('tb_status_pegawai')->orderBy('id', 'ASC')->get();
        foreach ($data as $key => $value) {
            $result[$key] = [
                'value' => $value->nama_status_pegawai
            ];
        }
        return response()->json($result);
    }

    public function pendidikanTerakhir()
    {
        $result = [];
        $data = DB::table('tb_pendidikan')->orderBy('id', 'DESC')->get();
        foreach ($data as $key => $value) {
            $result[$key] = [
                'value' => $value->nama_pendidikan
            ];
        }
        return response()->json($result);
    }

    public function optionEselon()
    {
        $result = [];
        $data = DB::table('tb_eselon')->orderBy('id', 'ASC')->get();
        foreach ($data as $key => $value) {
            $result[$key] = [
                'value' => $value->nama_eselon
            ];
        }
        return response()->json($result);
    }

    public function tesScheduling()
    {

        $absen = '';
        $data = DB::table('tb_pegawai')->whereNotExists(function ($query) {
            $query->select(DB::raw(1))->from('tb_absen')->where('tanggal_absen', date('Y-m-d'))->whereColumn('tb_absen.id_pegawai', 'tb_pegawai.id');
        })->get();

        foreach ($data as $key => $value) {
            for ($i = 0; $i < 2; $i++) {
                $absen = new absen();
                $absen->id_pegawai = $value->id;
                $absen->tanggal_absen = date('Y-m-d');
                $absen->status = 'alpa';
                if ($i == 0) {
                    $absen->jenis = 'checkin';
                    $absen->waktu_absen = '08:00:00';
                } else {
                    $absen->jenis = 'checkout';
                    $absen->waktu_absen = '17:00:00';
                }
                $absen->location_auth = 'valid';
                $absen->face_auth = 'valid';
                $absen->tahun = '2022';
                $absen->save();
            }
        }
    }

    public function reset_password(Request $request, $id)
    {
        $user = User::where('id', $id)->first();

        $user->password = Hash::make('dikerja');
        $user->save();

        // return $user->password;

        if ($user) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $user
            ]);
        } else {
            return response()->json([
                'message' => 'Failed',
                'status' => false,
            ], 422);
        }
    }
}
