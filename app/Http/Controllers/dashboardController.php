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
			return $this->opd_dashboard();
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
		// return Auth::user()->id_pegawai;
		$getSkp = skp::where('id_pegawai',Auth::user()->id_pegawai)->get()->count();
		$list_pegawai = [];
		$result = [];
		$temporary = [];
		$getPegawai = atasan::where('id_penilai',Auth::user()->id_pegawai)->get();
		$getAtasan = jabatan::where('id_pegawai',Auth::user()->id_pegawai)->first();
	
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
		}
	

		// INFO PEGAWAI
		$info_pegawai = [];
		$get_pegawai = pegawai::with('skp')->where('id',Auth::user()->id_pegawai)->first();
		if (isset($info_pegawai)) {
			$info_pegawai = [
				'nama' => $get_pegawai['nama'],
				'nip' => $get_pegawai['nip'],
				'pangkat' => $get_pegawai['golongan_pangkat'],
				'jabatan' => $get_pegawai['jenis_jabatan'],
				'Instansi' => $get_pegawai['satuan_kerja']['nama_satuan_kerja']
			];
		}
		$info_penilai = [];
		if (isset($getAtasan)) {
			// return $getAtasan;
			$get_penilai = pegawai::where('id',$getAtasan->id_pegawai)->first();
			// return $get_penilai;
			$info_penilai = [
				'nama' => $get_penilai['nama'],
				'nip' => $get_penilai['nip'],
				'pangkat' => $get_penilai['golongan_pangkat'],
				'jabatan' => $get_penilai['jenis_jabatan'],
				'Instansi' => $get_penilai['satuan_kerja']['nama_satuan_kerja']
			];	
		}
		
		// 

		// return $info_pegawai;

		$cek = [];
		$total_realisasi = 0;
		$total_target = 0;
		$total_kinerja = 0;
		$hasil_total_kinerja = 0;

		// INFO TPP
			$nilai_besaran_tpp = 0;
			$besaran_tpp = jabatan::where('id_pegawai',Auth::user()->id_pegawai)->first();
			if ($besaran_tpp) {
				$nilai_besaran_tpp = $besaran_tpp['kelas_jabatan']['besaran_tpp'];
			}
			// Tunjangan Prestasi Kerja
			// return $get_pegawai;
			if (isset($get_pegawai)) {
				if (!empty($get_pegawai['skp'])) {
					// return $get_pegawai['skp'];
					foreach ($get_pegawai['skp'] as $kk => $vv) {
						$aspek = aspek_skp::where('id_skp',$vv['id'])->get();
						$cek[$kk] = $aspek;
						foreach($aspek as $index => $val){
							foreach($val['realisasi_skp'] as $k => $kl){ 
								$total_realisasi += $kl['realisasi_bulanan'];
								$total_target += $val['target_skp'][$k]['target'];
							}
						}
					}
		
					if ($total_realisasi > 0 && $total_target > 0) {
						$total_kinerja = (($total_realisasi / $total_target) * 100) / $getSkp;
						$hasil_total_kinerja = ($total_kinerja / 60) * 100;
					}
				}
			}

				// return $total_realisasi;
			// 
		// 

		$result = [
    		'jumlah_skp' => $getSkp,
    		'pegawai_diniai' => count($getPegawai),
			'aktivitas' => $countAktivitas,
			'informasi_pegawai' => $info_pegawai,
			'informasi_penilai' => $info_penilai,
			'list_rekap_nilai' => $list_pegawai,
			'informasi_tpp' => [
				'besaran_tpp' => number_format($nilai_besaran_tpp,2),
				'tunjangan_prestasi_kerja' => number_format($hasil_total_kinerja,2)
			]
    	];

    	return $result;

	}

	public function opd_dashboard(){
		$result = [];
		$current = pegawai::where('id',Auth::user()->id_pegawai)->first();
		$pegawaiBySatuankerja = pegawai::where('id_satuan_kerja',$current['id_satuan_kerja'])->get();
		$count_aktifitas = 0;
		$count_skp = 0;
		// return $pegawaiBySatuankerja;
		foreach($pegawaiBySatuankerja as $k => $vv){
			// Aktifitias
			$aktivitas = aktivitas::where('id_pegawai',$vv['id'])->get()->count();
			$skp = skp::where('id_pegawai',$vv['id'])->get()->count();
			// return $aktivitas;
			if ($aktivitas != []) {
				$count_aktifitas += $aktivitas;
			}

			if ($skp != []) {
				$count_skp += $skp;
			}
				// 
		}

		return $result = [
			'jumlah_pegawai' => count($pegawaiBySatuankerja),
			'jumlah_aktifitas' => $count_aktifitas,
			'jumlah_skp' => $count_skp
		];	
		

	}


}
