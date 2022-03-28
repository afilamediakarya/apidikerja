<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\skp;
use App\Models\realisasi_skp;
use App\Models\aktivitas;
use App\Models\pegawai;
use App\Models\review_realisasi_skp;
use DB;
use Auth;
class dashboardController extends Controller
{
    public function get_data(){

		if (Auth::user()->role == 'super_admin') {
			return 'super_admin';
		} elseif(Auth::user()->role == 'super_admin') {
		
		}else{

		}
		

    	$result= [];
    	$skp = skp::all()->count();
 		$realisasi = realisasi_skp::all()->count();
 		$aktivitas = aktivitas::all()->count();
		$pegawai = pegawai::all()->count();
		$list_pegawai = [];
		$label_review = '';
		$label_review_skp = '';
		// $res = DB::table('tb_pegawai')->select('tb_pegawai.id','tb_pegawai.nama', 'tb_pegawai.nip', 'tb_pegawai.jenis_jabatan', 'tb_skp.id AS id_skp', 'tb_pegawai.id AS id_pegawai','tb_review.kesesuaian AS kesesuaian')->join('tb_skp','tb_pegawai.id', '=', 'tb_skp.id_pegawai')->join('tb_review','tb_skp.id','=','tb_review.id_skp')->join('tb_review_realisasi_skp','tb_skp.id','=','tb_review_realisasi_skp.id_skp')->get();
		

		$res = DB::table('tb_pegawai')->select('tb_pegawai.id','tb_pegawai.nama', 'tb_pegawai.nip', 'tb_pegawai.jenis_jabatan', 'tb_skp.id AS id_skp', 'tb_pegawai.id AS id_pegawai','tb_review.kesesuaian AS kesesuaian')->join('tb_skp','tb_pegawai.id', '=', 'tb_skp.id_pegawai')->join('tb_review','tb_skp.id','=','tb_review.id_skp')->get();

		foreach ($res as $key => $value) {
			$review_realisasi = review_realisasi_skp::where('id_skp',$value->id_skp)->get()->pluck('kesesuaian')->toArray();

			if ($value->kesesuaian == 'ya') {
				$label_review = 'Selesai';
			} else {
				$label_review = 'Belum Review';
			}

			if (in_array("tidak", $review_realisasi) == true && in_array("ya", $review_realisasi) == true){
				$label_review_skp = 'Belum Sesuai';
			}
			else if(in_array("ya", $review_realisasi) == true && in_array("tidak", $review_realisasi) == false){
				$label_review_skp = 'Selesai';
			}else{
				$label_review_skp = 'Belum Review';
			}	
			
			$list_pegawai[$key] = [
				'nama' => $value->nama,
				'review_skp' => $label_review,
				'review_realisasi' => $label_review_skp
			];
		}

    	$result = [
    		'jumlah_skp' => $skp,
    		'jumlah_realisasi' => $realisasi,
    		'jumlah_aktivitas' => $aktivitas,
    		'jumlah_pegawai' => $pegawai,
			'list_rekap_nilai' => $list_pegawai
    	];

    	return $result;
    }


}
