<?php

namespace App\Http\Controllers;

use App\Models\riwayatJabatan;
use App\Models\riwayatKepangkatan;
use App\Models\riwayatPendidikan;
use App\Models\riwayatPendidikanNonformal;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    public function personalData()
    {
        $pegawai = DB::table('tb_pegawai')
            ->select("tb_pegawai.id", "tb_pegawai.nama", "tb_pegawai.nip", "tb_pegawai.tempat_lahir", "tb_pegawai.tanggal_lahir", "tb_pegawai.jenis_kelamin", "tb_pegawai.status_perkawinan", "tb_pegawai.agama", "tb_pegawai.pendidikan", "tb_pegawai.jurusan", "tb_pegawai.lulus_pendidikan", "tb_pegawai.pendidikan_struktural", "tb_pegawai.lulus_pendidikan_struktural", "tb_pegawai.golongan", "tb_pegawai.tmt_golongan", "tb_pegawai.tmt_pegawai", "tb_jabatan.nama_jabatan", "tb_satuan_kerja.nama_satuan_kerja")
            ->join("tb_jabatan", "tb_jabatan.id_pegawai", "tb_pegawai.id")
            ->join("tb_satuan_kerja", "tb_satuan_kerja.id", "tb_pegawai.id_satuan_kerja")
            ->where("tb_pegawai.id", Auth::user()->id_pegawai)
            ->first();

        if ($pegawai !== null) {
            return response()->json([
                "code" => "200",
                "status" => "OK",
                "data" => $pegawai
            ], 200);
        } else {
            return response()->json([
                "code" => "404",
                "status" => "Pegawai not found"
            ], 404);
        }
    }

    public function getListPendidikan()
    {
        $pendidikan = DB::table("tb_pendidikan")
            ->select("id", "nama_pendidikan")
            ->get();

        return $pendidikan;
    }

    public function getListGolongan()
    {
        $golongan = DB::table("tb_golongan")
            ->select("id", "nama_golongan")
            ->get();

        return $golongan;
    }

    public function getListUnitkerja()
    {
        $unitKerja = DB::table("tb_satuan_kerja")
            ->select("id", "nama_satuan_kerja")
            ->get();

        return $unitKerja;
    }


    // pendidikan formal
    public function listPendidikanFormal(Request $request)
    {
        $data = DB::table('tb_riwayat_pendidikan')
            ->select('tb_riwayat_pendidikan.*', 'tb_pegawai.nama', 'tb_pendidikan.nama_pendidikan')
            ->join('tb_pegawai', 'tb_riwayat_pendidikan.id_pegawai', '=', 'tb_pegawai.id')
            ->join('tb_pendidikan', 'tb_riwayat_pendidikan.id_pendidikan', '=', 'tb_pendidikan.id')
            ->where('tb_riwayat_pendidikan.id_pegawai', Auth::user()->id_pegawai)
            ->where('tb_riwayat_pendidikan.jenis_pendidikan', 'formal')
            ->orderBy('tb_riwayat_pendidikan.id_pendidikan', 'ASC')
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
                'status' => false,
                'data' => $data
            ]);
        }
    }

    public function getPendidikanFormal(Request $request, $id)
    {
        $data = DB::table('tb_riwayat_pendidikan')
            ->select('tb_riwayat_pendidikan.*', 'tb_pegawai.nama', 'tb_pendidikan.nama_pendidikan')
            ->join('tb_pegawai', 'tb_riwayat_pendidikan.id_pegawai', '=', 'tb_pegawai.id')
            ->join('tb_pendidikan', 'tb_riwayat_pendidikan.id_pendidikan', '=', 'tb_pendidikan.id')
            ->where('tb_riwayat_pendidikan.id_pegawai', Auth::user()->id_pegawai)
            ->where('tb_riwayat_pendidikan.jenis_pendidikan', 'formal')
            ->where('tb_riwayat_pendidikan.id', $id)
            ->orderBy('tb_riwayat_pendidikan.id_pendidikan', 'ASC')
            ->first();


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

    public function storePendidikanFormal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_pegawai' => 'required|numeric',
            'id_pendidikan' => 'required|numeric',
            'fakultas' => 'required',
            'jurusan' => 'required',
            'nomor_ijazah' => 'required',
            'tanggal_ijazah' => 'required',
            'nama_kepala_sekolah' => 'required',
            'nama_sekolah' => 'required',
            'alamat_sekolah' => 'required',
            'foto_ijazah' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $data = new riwayatPendidikan();
        $data->id_pegawai = $request->id_pegawai;
        $data->id_pendidikan = $request->id_pendidikan;
        $data->jenis_pendidikan = $request->jenis_pendidikan;
        $data->fakultas = $request->fakultas;
        $data->jurusan = $request->jurusan;
        $data->nomor_ijazah = $request->nomor_ijazah;
        $data->tanggal_ijazah = $request->tanggal_ijazah;
        $data->nama_kepala_sekolah = $request->nama_kepala_sekolah;
        $data->nama_sekolah = $request->nama_sekolah;
        $data->alamat_sekolah = $request->alamat_sekolah;
        $data->document_formal = $request->foto_ijazah;
        $data->verifikasi = 0;
        $data->id_pegawai_verifikator = 0;
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

    public function updatePendidikanFormal(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'id_pegawai' => 'required|numeric',
            'id_pendidikan' => 'required|numeric',
            'fakultas' => 'required',
            'jurusan' => 'required',
            'nomor_ijazah' => 'required',
            'tanggal_ijazah' => 'required',
            'nama_kepala_sekolah' => 'required',
            'nama_sekolah' => 'required',
            'alamat_sekolah' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $data = riwayatPendidikan::where('id', $id)->first();
        $data->id_pegawai = $request->id_pegawai;
        $data->id_pendidikan = $request->id_pendidikan;
        $data->jenis_pendidikan = $request->jenis_pendidikan;
        $data->fakultas = $request->fakultas;
        $data->jurusan = $request->jurusan;
        $data->nomor_ijazah = $request->nomor_ijazah;
        $data->tanggal_ijazah = $request->tanggal_ijazah;
        $data->nama_kepala_sekolah = $request->nama_kepala_sekolah;
        $data->nama_sekolah = $request->nama_sekolah;
        $data->alamat_sekolah = $request->alamat_sekolah;

        if ($request->foto_ijazah !== null) {
            $data->document_formal = $request->foto_ijazah;
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

    public function deletePendidikanFormal($id)
    {
        $data = riwayatPendidikan::where('id', $id)->first();
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
    // end pendidikan formal


    // pendidikan nonformal
    public function listPendidikanNonFormal(Request $request)
    {
        $data = DB::table('tb_riwayat_pendidikan_nonformal')
            ->select('tb_riwayat_pendidikan_nonformal.*', 'tb_pegawai.nama',)
            ->join('tb_pegawai', 'tb_riwayat_pendidikan_nonformal.id_pegawai', '=', 'tb_pegawai.id')
            ->where('tb_riwayat_pendidikan_nonformal.id_pegawai', Auth::user()->id_pegawai)
            // ->where('tb_riwayat_pendidikan_nonformal.jenis_pendidikan', 'nonformal')
            ->orderBy('tb_riwayat_pendidikan_nonformal.tanggal_ijazah', 'ASC')
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
                'status' => false,
                'data' => $data
            ]);
        }
    }

    public function storePendidikanNonFormal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_pegawai' => 'required|numeric',
            'nama_kursus' => 'required',
            'tanggal_mulai' => 'required',
            'tanggal_akhir' => 'required',
            'nomor_ijazah' => 'required',
            'tanggal_ijazah' => 'required',
            'nama_pejabat' => 'required',
            'instansi_penyelenggara' => 'required',
            'tempat' => 'required',
            'document_nonformal' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }


        $data = new riwayatPendidikanNonformal();
        $data->id_pegawai = $request->id_pegawai;
        $data->jenis_pendidikan = $request->jenis_pendidikan;
        $data->nama_kursus = $request->nama_kursus;
        $data->tanggal_mulai = $request->tanggal_mulai;
        $data->tanggal_akhir = $request->tanggal_akhir;
        $data->nomor_ijazah = $request->nomor_ijazah;
        $data->tanggal_ijazah = $request->tanggal_ijazah;
        $data->nama_pejabat = $request->nama_pejabat;
        $data->instansi_penyelenggara = $request->instansi_penyelenggara;
        $data->tempat = $request->tempat;
        $data->document_nonformal = $request->document_nonformal;
        $data->verifikasi = 0;
        $data->id_pegawai_verifikator = 0;
        $data->save();
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

    public function getPendidikanNonFormal(Request $request, $id)
    {
        $data = DB::table('tb_riwayat_pendidikan_nonformal')
            ->select('tb_riwayat_pendidikan_nonformal.*', 'tb_pegawai.nama',)
            ->join('tb_pegawai', 'tb_riwayat_pendidikan_nonformal.id_pegawai', '=', 'tb_pegawai.id')
            ->where('tb_riwayat_pendidikan_nonformal.id_pegawai', Auth::user()->id_pegawai)
            // ->where('tb_riwayat_pendidikan_nonformal.jenis_pendidikan', 'nonformal')
            ->where('tb_riwayat_pendidikan_nonformal.id', $id)
            ->first();
        // dd($data);
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

    public function updatePendidikanNonFormal(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'id_pegawai' => 'required|numeric',
            'nama_kursus' => 'required',
            'tanggal_mulai' => 'required',
            'tanggal_akhir' => 'required',
            'nomor_ijazah' => 'required',
            'tanggal_ijazah' => 'required',
            'nama_pejabat' => 'required',
            'instansi_penyelenggara' => 'required',
            'tempat' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $data = riwayatPendidikanNonformal::where('id', $id)->first();
        $data->id_pegawai = $request->id_pegawai;
        $data->jenis_pendidikan = $request->jenis_pendidikan;
        $data->nama_kursus = $request->nama_kursus;
        $data->tanggal_mulai = $request->tanggal_mulai;
        $data->tanggal_akhir = $request->tanggal_akhir;
        $data->tanggal_akhir = $request->tanggal_akhir;
        $data->nomor_ijazah = $request->nomor_ijazah;
        $data->tanggal_ijazah = $request->tanggal_ijazah;
        $data->nama_pejabat = $request->nama_pejabat;
        $data->instansi_penyelenggara = $request->instansi_penyelenggara;
        $data->tempat = $request->tempat;
        $data->verifikasi = 0;
        $data->id_pegawai_verifikator = 0;
        $data->save();

        if ($request->document_nonformal !== null) {
            $data->document_nonformal = $request->document_nonformal;
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

    public function deletePendidikanNonFormal($id)
    {
        $data = riwayatPendidikanNonformal::where('id', $id)->first();
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
    // end pendidikan nonformal

    // kepangkatan
    public function listKepangkatan(Request $request)
    {
        $data = DB::table('tb_riwayat_kepangkatan')
            ->select('tb_riwayat_kepangkatan.*', 'tb_pegawai.nama', 'tb_golongan.nama_golongan', 'tb_satuan_kerja.nama_satuan_kerja')
            ->join('tb_pegawai', 'tb_riwayat_kepangkatan.id_pegawai', '=', 'tb_pegawai.id')
            ->join('tb_golongan', 'tb_riwayat_kepangkatan.id_golongan', '=', 'tb_golongan.id')
            ->join('tb_satuan_kerja', 'tb_riwayat_kepangkatan.id_satuan_kerja', '=', 'tb_satuan_kerja.id')
            ->where('tb_riwayat_kepangkatan.id_pegawai', Auth::user()->id_pegawai)
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
                'status' => false,
                'data' => $data
            ]);
        }
    }

    public function storeKepangkatan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_pegawai' => 'required|numeric',
            'id_golongan' => 'required',
            'jenis_kenaikan_pangkat' => 'required',
            'tahun_kerja' => 'required',
            'bulan_kerja' => 'required',
            'gaji_pokok' => 'required',
            'nomor_sk' => 'required',
            'tanggal_sk' => 'required',
            'nomor_nota' => 'required',
            'tanggal_nota' => 'required',
            'nama_pejabat' => 'required',
            'tmt' => 'required',
            'id_satuan_kerja' => 'required',
            'document_kepangkatan' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $data = new riwayatKepangkatan();
        $data->id_pegawai = $request->id_pegawai;
        $data->id_golongan = $request->id_golongan;
        $data->jenis_kenaikan_pangkat = $request->jenis_kenaikan_pangkat;
        $data->tahun_kerja = $request->tahun_kerja;
        $data->bulan_kerja = $request->bulan_kerja;
        $data->gaji_pokok = $request->gaji_pokok;
        $data->nomor_sk = $request->nomor_sk;
        $data->tanggal_sk = $request->tanggal_sk;
        $data->nomor_nota = $request->nomor_nota;
        $data->tanggal_nota = $request->tanggal_nota;
        $data->nama_pejabat = $request->nama_pejabat;
        $data->tmt = $request->tmt;
        $data->id_satuan_kerja = $request->id_satuan_kerja;
        $data->document_kepangkatan = $request->document_kepangkatan;
        $data->verifikasi = 0;
        $data->id_pegawai_verifikator = 0;
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

    public function getKepangkatan(Request $request, $id)
    {
        $data = DB::table('tb_riwayat_kepangkatan')
            ->select('tb_riwayat_kepangkatan.*', 'tb_pegawai.nama', 'tb_golongan.nama_golongan', 'tb_satuan_kerja.nama_satuan_kerja',)
            ->join('tb_pegawai', 'tb_riwayat_kepangkatan.id_pegawai', '=', 'tb_pegawai.id')
            ->join('tb_golongan', 'tb_riwayat_kepangkatan.id_golongan', '=', 'tb_golongan.id')
            ->join('tb_satuan_kerja', 'tb_riwayat_kepangkatan.id_satuan_kerja', '=', 'tb_satuan_kerja.id')
            ->where('tb_riwayat_kepangkatan.id_pegawai', Auth::user()->id_pegawai)
            ->where('tb_riwayat_kepangkatan.id', $id)
            ->first();
        // dd($data);
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

    public function updateKepangkatan(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'id_pegawai' => 'required|numeric',
            'id_golongan' => 'required',
            'jenis_kenaikan_pangkat' => 'required',
            'tahun_kerja' => 'required',
            'bulan_kerja' => 'required',
            'gaji_pokok' => 'required',
            'nomor_sk' => 'required',
            'tanggal_sk' => 'required',
            'nomor_nota' => 'required',
            'tanggal_nota' => 'required',
            'nama_pejabat' => 'required',
            'tmt' => 'required',
            'id_satuan_kerja' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $data = riwayatKepangkatan::where('id', $id)->first();
        $data->id_pegawai = $request->id_pegawai;
        $data->id_golongan = $request->id_golongan;
        $data->jenis_kenaikan_pangkat = $request->jenis_kenaikan_pangkat;
        $data->tahun_kerja = $request->tahun_kerja;
        $data->bulan_kerja = $request->bulan_kerja;
        $data->gaji_pokok = $request->gaji_pokok;
        $data->nomor_sk = $request->nomor_sk;
        $data->tanggal_sk = $request->tanggal_sk;
        $data->nomor_nota = $request->nomor_nota;
        $data->tanggal_nota = $request->tanggal_nota;
        $data->nama_pejabat = $request->nama_pejabat;
        $data->tmt = $request->tmt;
        $data->id_satuan_kerja = $request->id_satuan_kerja;
        $data->verifikasi = 0;
        $data->id_pegawai_verifikator = 0;
        $data->save();

        if ($request->document_kepangkatan !== null) {
            $data->document_kepangkatan = $request->document_kepangkatan;
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

    public function deleteKepangkatan($id)
    {
        $data = riwayatKepangkatan::where('id', $id)->first();
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
    // end kepangkatan

    // jabatan
    public function listJabatan(Request $request)
    {
        $data = DB::table('tb_riwayat_jabatan')
            ->select('tb_riwayat_jabatan.*', 'tb_pegawai.nama', 'tb_golongan.nama_golongan', 'tb_satuan_kerja.nama_satuan_kerja')
            ->join('tb_pegawai', 'tb_riwayat_jabatan.id_pegawai', '=', 'tb_pegawai.id')
            ->join('tb_golongan', 'tb_riwayat_jabatan.id_golongan', '=', 'tb_golongan.id')
            ->join('tb_satuan_kerja', 'tb_riwayat_jabatan.id_satuan_kerja', '=', 'tb_satuan_kerja.id')
            ->where('tb_riwayat_jabatan.id_pegawai', Auth::user()->id_pegawai)
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
                'status' => false,
                'data' => $data
            ]);
        }
    }

    public function storeJabatan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_pegawai' => 'required|numeric',
            'nama_jabatan' => 'required',
            'id_golongan' => 'required',
            'keterangan_jabatan' => 'required',
            'tipe_jabatan' => 'required',
            'jenis_jabatan' => 'required',
            'nomor_sk' => 'required',
            'tanggal_sk' => 'required',
            'nama_pejabat' => 'required',
            'tmt' => 'required',
            'id_satuan_kerja' => 'required',
            'document_jabatan' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $data = new riwayatJabatan();
        $data->id_pegawai = $request->id_pegawai;
        $data->id_golongan = $request->id_golongan;
        $data->keterangan_jabatan = $request->keterangan_jabatan;
        $data->tipe_jabatan = $request->tipe_jabatan;
        $data->jenis_jabatan = $request->jenis_jabatan;
        $data->nama_jabatan = $request->nama_jabatan;
        $data->nomor_sk = $request->nomor_sk;
        $data->tanggal_sk = $request->tanggal_sk;
        $data->nama_pejabat = $request->nama_pejabat;
        $data->tmt = $request->tmt;
        $data->id_satuan_kerja = $request->id_satuan_kerja;
        $data->document_jabatan = $request->document_jabatan;
        $data->verifikasi = 0;
        $data->id_pegawai_verifikator = 0;
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

    public function getJabatan(Request $request, $id)
    {
        $data = DB::table('tb_riwayat_jabatan')
            ->select('tb_riwayat_jabatan.*', 'tb_pegawai.nama', 'tb_golongan.nama_golongan', 'tb_satuan_kerja.nama_satuan_kerja',)
            ->join('tb_pegawai', 'tb_riwayat_jabatan.id_pegawai', '=', 'tb_pegawai.id')
            ->join('tb_golongan', 'tb_riwayat_jabatan.id_golongan', '=', 'tb_golongan.id')
            ->join('tb_satuan_kerja', 'tb_riwayat_jabatan.id_satuan_kerja', '=', 'tb_satuan_kerja.id')
            ->where('tb_riwayat_jabatan.id_pegawai', Auth::user()->id_pegawai)
            ->where('tb_riwayat_jabatan.id', $id)
            ->first();
        // dd($data);
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

    public function updateJabatan(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'id_pegawai' => 'required|numeric',
            'nama_jabatan' => 'required',
            'id_golongan' => 'required',
            'keterangan_jabatan' => 'required',
            'tipe_jabatan' => 'required',
            'jenis_jabatan' => 'required',
            'nomor_sk' => 'required',
            'tanggal_sk' => 'required',
            'nama_pejabat' => 'required',
            'tmt' => 'required',
            'id_satuan_kerja' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $data = riwayatJabatan::where('id', $id)->first();
        $data->id_pegawai = $request->id_pegawai;
        $data->id_golongan = $request->id_golongan;
        $data->keterangan_jabatan = $request->keterangan_jabatan;
        $data->tipe_jabatan = $request->tipe_jabatan;
        $data->jenis_jabatan = $request->jenis_jabatan;
        $data->nama_jabatan = $request->nama_jabatan;
        $data->nomor_sk = $request->nomor_sk;
        $data->tanggal_sk = $request->tanggal_sk;
        $data->nama_pejabat = $request->nama_pejabat;
        $data->tmt = $request->tmt;
        $data->id_satuan_kerja = $request->id_satuan_kerja;
        $data->verifikasi = 0;
        $data->id_pegawai_verifikator = 0;
        $data->save();

        if ($request->document_jabatan !== null) {
            $data->document_jabatan = $request->document_jabatan;
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

    public function deleteJabatan($id)
    {
        $data = riwayatJabatan::where('id', $id)->first();
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
    // end jabatan

}
