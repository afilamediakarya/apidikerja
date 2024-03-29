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
            $pegawaiBySatuanKerja = DB::table('tb_pegawai')->select('tb_pegawai.id', 'tb_pegawai.nama', 'tb_pegawai.nip', 'tb_pegawai.golongan', 'tb_pegawai.jenis_jabatan', 'tb_jabatan.nama_jabatan', 'tb_jabatan.nilai_jabatan','tb_jabatan.pembayaran_tpp', 'tb_jabatan.id_jenis_jabatan', 'tb_jenis_jabatan.level','tb_jabatan.target_waktu','tb_jabatan.kelas_jabatan')
                ->join('tb_jabatan', 'tb_pegawai.id', '=', 'tb_jabatan.id_pegawai')
                ->join('tb_jenis_jabatan', 'tb_jabatan.id_jenis_jabatan', '=', 'tb_jenis_jabatan.id')
                ->where('tb_pegawai.id_satuan_kerja', $satuanKerja)
                // ->where('tb_pegawai.id', 256)
                ->orderBy('tb_jabatan.kelas_jabatan', 'desc')
                ->get();
        } else {
            $pegawai = DB::table('tb_pegawai')->select('id_satuan_kerja')->where('id', Auth::user()->id_pegawai)->first();

            $pegawaiBySatuanKerja = DB::table('tb_pegawai')->select('tb_pegawai.id', 'tb_pegawai.nama', 'tb_pegawai.nip', 'tb_pegawai.golongan', 'tb_pegawai.jenis_jabatan', 'tb_jabatan.nama_jabatan', 'tb_jabatan.nilai_jabatan','tb_jabatan.pembayaran_tpp', 'tb_jabatan.id_jenis_jabatan', 'tb_jenis_jabatan.level','tb_jabatan.target_waktu','tb_jabatan.kelas_jabatan')
                ->join('tb_jabatan', 'tb_pegawai.id', '=', 'tb_jabatan.id_pegawai')
                ->join('tb_jenis_jabatan', 'tb_jabatan.id_jenis_jabatan', '=', 'tb_jenis_jabatan.id')
                ->where('id_satuan_kerja', $pegawai->id_satuan_kerja)
                ->orderBy('tb_jabatan.kelas_jabatan', 'desc')
                ->orderBy('tb_pegawai.nama','asc')
                ->orderBy('nama', 'asc')
                ->get();
        }

        foreach ($pegawaiBySatuanKerja as $key => $value) {

            $result = [];
            $jabatanByPegawai = DB::table('tb_jabatan')->where('id_pegawai', $value->id)->first();

            $get_kinerja = DB::table('tb_aktivitas')
                            ->select(DB::raw("SUM(waktu) as count"))
                           ->where('id_pegawai',$value->id)
                           ->where('kesesuaian','1')
                           ->whereMonth('tanggal',$bulan)
                           ->first();
            $value->get_kinerja = $get_kinerja;

            $get_absen = (new laporanRekapitulasiabsenController)->rekapByUser($startDate, $endDate, $value->id);
            $datax = $get_absen->getData();
 
            $value->persentase_pemotongan = round($datax->data->jml_potongan_kehadiran_kerja, 2);
            $value->jumlah_alpa = $datax->data->tanpa_keterangan;
    
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
