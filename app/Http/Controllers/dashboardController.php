<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\skp;
use App\Models\realisasi_skp;
use App\Models\aktivitas;
use App\Models\pegawai;
use App\Models\review_realisasi_skp;
use App\Models\atasan;
use App\Models\kelas_jabatan;
use App\Models\jabatan;
use App\Models\aspek_skp;
use DB;
use Auth;
class dashboardController extends Controller
{
    public function get_data(){

		if (Auth::user()->role == 'super_admin') {
			return $this->admin_dashboard();
		} elseif(Auth::user()->role == 'pegawai') {
			return $this->pegawai_dashboard();
		}else{

		}
    }

	public function admin_dashboard(){
		$result= [];
    	$skp = skp::all()->count();
 		$realisasi = realisasi_skp::all()->count();
 		$aktivitas = aktivitas::all()->count();
		$pegawai = pegawai::all()->count();
		$list_pegawai = [];
		$label_review = '';
		$label_review_skp = '';
		

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

	public function pegawai_dashboard(){
		$getSkp = [];
		$list_pegawai = [];
		$result = [];
		$temporary = [];
		$total_realisasi = 0;
		$getPegawai = atasan::where('id_penilai',Auth::user()->id_pegawai)->get();
		$getAtasan = atasan::where('id_pegawai',Auth::user()->id_pegawai)->first();
		$countAktivitas = aktivitas::where('id_pegawai',Auth::user()->id_pegawai)->count();

		foreach ($getPegawai as $key => $value) {
		
			$res = DB::table('tb_pegawai')->select('tb_pegawai.id','tb_pegawai.nama', 'tb_pegawai.nip', 'tb_pegawai.jenis_jabatan', 'tb_skp.id AS id_skp', 'tb_pegawai.id AS id_pegawai','tb_review.kesesuaian AS kesesuaian')->join('tb_skp','tb_pegawai.id', '=', 'tb_skp.id_pegawai')->join('tb_review','tb_skp.id','=','tb_review.id_skp')->where('tb_pegawai.id',$value->id_pegawai)->get();

			foreach ($res as $x => $y) {
				$review_realisasi = review_realisasi_skp::where('id_skp',$y->id_skp)->get()->pluck('kesesuaian')->toArray();
	
				if ($y->kesesuaian == 'ya') {
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
					'nama' => $y->nama,
					'review_skp' => $label_review,
					'review_realisasi' => $label_review_skp
				];
			}

			$getSkp[$key] = $res;
		}
	

		// INFO PEGAWAI
		$info_pegawai = pegawai::with('skp')->where('id',Auth::user()->id_pegawai)->first();
		$info_penilai = pegawai::where('id',$getAtasan->id_penilai)->first();
		// 

		$cek = [];

		// INFO TPP
			$besaran_tpp = jabatan::where('id_pegawai',Auth::user()->id_pegawai)->first();

			return $info_pegawai->skp['id'];
			// Tunjangan Prestasi Kerja
				$aspek = aspek_skp::where('id_skp',$info_pegawai->skp['id'])->get();
				$total_realisasi = 0;
				$total_target = 0;
				$persentase_awal_kinerja = 0;
				foreach($aspek as $index => $val){
					for ($in=0; $in < count($val['realisasi_skp']); $in++) { 
						$total_realisasi += $val['realisasi_skp'][$in]['realisasi_bulanan'];
						$total_target += $val['target_skp'][$in]['target'];
						$cek[$in] = $val['realisasi_skp'][$in]['realisasi_bulanan'];
					}					
					$persentase_awal_kinerja = ($total_realisasi / $total_target) * 100;
				}

				return $cek;
			// 
		// 

		$result = [
    		'jumlah_skp' => count($getSkp),
    		'pegawai_diniai' => count($getPegawai),
			'aktivitas' => $countAktivitas,
			'informasi_pegawai' => [
				'nama' => $info_pegawai['nama'],
				'nip' => $info_pegawai['nip'],
				'pangkat' => $info_pegawai['golongan_pangkat'],
				'jabatan' => $info_pegawai['jenis_jabatan'],
				'Instansi' => $info_pegawai['satuan_kerja']['nama_satuan_kerja']
			],
			'informasi_penilai' => [
				'nama' => $info_penilai['nama'],
				'nip' => $info_penilai['nip'],
				'pangkat' => $info_penilai['golongan_pangkat'],
				'jabatan' => $info_penilai['jenis_jabatan'],
				'Instansi' => $info_penilai['satuan_kerja']['nama_satuan_kerja']
			],
			'list_rekap_nilai' => $list_pegawai,
			'informasi_tpp' => [
				'besaran_tpp' => $besaran_tpp['kelas_jabatan']['besaran_tpp']
			]
    	];

    	return $result;

	}


}
