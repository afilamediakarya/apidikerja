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
	public function get_data($type)
	{
		if ($type == 'super_admin') {
			return $this->admin_dashboard();
		} elseif ($type == 'pegawai') {
			return $this->pegawai_dashboard();
		} else {
			return $this->opd_dashboard();
		}
	}

	public function admin_dashboard()
	{
		$result = [];
		$skp = skp::all()->count();
		$realisasi = realisasi_skp::all()->count();
		$aktivitas = aktivitas::all()->count();
		$pegawai = pegawai::all()->count();
		$list_pegawai = [];
		$label_review = '';
		$label_review_skp = '';


		$res = DB::table('tb_pegawai')->select('tb_pegawai.id', 'tb_pegawai.nama', 'tb_pegawai.nip', 'tb_pegawai.jenis_jabatan', 'tb_skp.id AS id_skp', 'tb_pegawai.id AS id_pegawai', 'tb_review.kesesuaian AS kesesuaian')->join('tb_skp', 'tb_pegawai.id', '=', 'tb_skp.id_pegawai')->join('tb_review', 'tb_skp.id', '=', 'tb_review.id_skp')->get();

		foreach ($res as $key => $value) {
			$review_realisasi = review_realisasi_skp::where('id_skp', $value->id_skp)->get()->pluck('kesesuaian')->toArray();

			if ($value->kesesuaian == 'ya') {
				$label_review = 'Selesai';
			} else {
				$label_review = 'Belum Review';
			}

			if (in_array("tidak", $review_realisasi) == true && in_array("ya", $review_realisasi) == true) {
				$label_review_skp = 'Belum Sesuai';
			} else if (in_array("ya", $review_realisasi) == true && in_array("tidak", $review_realisasi) == false) {
				$label_review_skp = 'Selesai';
			} else {
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

	public function pegawai_dashboard()
	{
		$jumlah_realisasi_skp = 0;
		$data_skp = DB::table('tb_skp')
			->select('tb_skp.id')
			->join('tb_jabatan', 'tb_skp.id_jabatan', 'tb_jabatan.id')
			->join('tb_pegawai', 'tb_jabatan.id_pegawai', '=', 'tb_pegawai.id')
			->where('tb_jabatan.id_pegawai', Auth::user()->id_pegawai)
			->get();

		$getSkp = [];
		foreach ($data_skp as $key => $value) {
			$get_target_skp = DB::table('tb_aspek_skp')
				->select('tb_skp.id as id_skp', 'tb_skp.rencana_kerja', 'tb_aspek_skp.id as id_aspek_skp', 'tb_target_skp.target', 'tb_target_skp.bulan as target_bulan')
				->join('tb_skp', 'tb_aspek_skp.id_skp', '=', 'tb_skp.id')
				->join('tb_target_skp', 'tb_aspek_skp.id', '=', 'tb_target_skp.id_aspek_skp')
				->where('tb_aspek_skp.id_skp', $value->id)
				->where('tb_target_skp.bulan', (trim(date('m'), 0)))
				->first();

			if (isset($get_target_skp)) {
				$getSkp[] = $get_target_skp;
			}
		}

		foreach ($getSkp as $k => $v) {
			$realisasi = DB::table('tb_review_realisasi_skp')->where('id_skp', $v->id_skp)
				->where('bulan', trim(date('m'), "0"))
				->where('kesesuaian', 'ya')
				->first();
			if (!is_null($realisasi)) {
				$jumlah_realisasi_skp += 1;
			}
		}
		// return $target_skp;

		$list_pegawai = [];
		$result = [];
		$temporary = [];
		$getJabatanByCurrentParent = [];

		$getJabatanPegawai = DB::table('tb_jabatan')->where('id_pegawai', Auth::user()->id_pegawai)->first();
		// return $getJabatanPegawai;
		if (isset($getJabatanPegawai)) {
			$getJabatanAtasan = DB::table('tb_jabatan')->where('id', $getJabatanPegawai->parent_id)->first();
			$getJabatanByCurrentParent = jabatan::where('parent_id', $getJabatanPegawai->id)->get();
		}

		// return $getJabatanPegawai;

		// AKTIVITAS
		$countAktivitas = aktivitas::where('id_pegawai', Auth::user()->id_pegawai)->count();

		if (count($getJabatanByCurrentParent) > 0) {
			foreach ($getJabatanByCurrentParent as $key => $value) {
				// return $value;

				$res = DB::table('tb_pegawai')->select('tb_pegawai.id', 'tb_pegawai.nama', 'tb_pegawai.nip', 'tb_pegawai.jenis_jabatan', 'tb_skp.id AS id_skp', 'tb_pegawai.id AS id_pegawai', 'tb_review.kesesuaian AS kesesuaian')->join('tb_jabatan', 'tb_pegawai.id', '=', 'tb_jabatan.id_pegawai')->join('tb_skp', 'tb_jabatan.id', '=', 'tb_skp.id_jabatan')->join('tb_review', 'tb_skp.id', '=', 'tb_review.id_skp')->where('tb_jabatan.id', $value->id)->get();


				if (count($res) > 0) {
					foreach ($res as $x => $y) {
						$review_realisasi = review_realisasi_skp::where('id_skp', $y->id_skp)->get()->pluck('kesesuaian')->toArray();

						if ($y->kesesuaian == 'ya') {
							$label_review = 'Selesai';
						} else {
							$label_review = 'Belum Review';
						}

						if (in_array("tidak", $review_realisasi) == true && in_array("ya", $review_realisasi) == true) {
							$label_review_skp = 'Belum Sesuai';
						} else if (in_array("ya", $review_realisasi) == true && in_array("tidak", $review_realisasi) == false) {
							$label_review_skp = 'Selesai';
						} else {
							$label_review_skp = 'Belum Review';
						}

						$list_pegawai[$key] = [
							'nama' => $y->nama,
							'review_skp' => $label_review,
							'review_realisasi' => $label_review_skp
						];
					}
				}
			}
		}


		// INFO PEGAWAI
		$info_pegawai = [];
		$get_pegawai = pegawai::join('tb_jabatan', 'tb_pegawai.id', '=', 'tb_jabatan.id_pegawai')
			->join('tb_jenis_jabatan', 'tb_jabatan.id_jenis_jabatan', '=', 'tb_jenis_jabatan.id')
			->where('tb_pegawai.id', Auth::user()->id_pegawai)->first();

		// return $get_pegawai;
		if (isset($info_pegawai)) {
			$info_pegawai = [
				'nama' => $get_pegawai['nama'],
				'nip' => $get_pegawai['nip'],
				'pangkat' => $get_pegawai['golongan'],
				'jabatan' => isset($getJabatanPegawai->nama_jabatan) ? $getJabatanPegawai->nama_jabatan : '-',
				'Instansi' => $get_pegawai['satuan_kerja']['nama_satuan_kerja'],
				'level_jabatan' => $get_pegawai['level']
			];
		}

		// INFO PENILAI
		$info_penilai = [];
		if (isset($getJabatanAtasan)) {

			$get_penilai = pegawai::where('id', $getJabatanAtasan->id_pegawai)->first();

			$info_penilai = [
				'nama' => $get_penilai['nama'],
				'nip' => $get_penilai['nip'],
				'pangkat' => $get_penilai['golongan'],
				'jabatan' => $getJabatanAtasan->nama_jabatan,
				'Instansi' => $get_penilai['satuan_kerja']['nama_satuan_kerja']
			];
		}

		// INFO TPP
		$cek = [];
		$total_realisasi = 0;
		$total_target = 0;
		$total_kinerja = 0;
		$hasil_total_kinerja = 0;

		$nilai_besaran_tpp = 0;
		$nilai_besaran_tpp = $get_pegawai['nilai_jabatan'];

		// if (isset($data_skp)) {

		// 	$result_skp = [];
		// 	$bulan = trim(date('m'), "0");
		// 	foreach ($data_skp as $key => $value) {
		// 		$get_data_skp =
		// 			skp::with(['aspek_skp' => function ($query) use ($bulan) {
		// 				$query->with(['target_skp' => function ($select) use ($bulan) {
		// 					$select->where('bulan', "{$bulan}");
		// 				}])
		// 					->with(['realisasi_skp' => function ($select) use ($bulan) {
		// 						$select->where('bulan', "{$bulan}");
		// 					}]);
		// 			}])
		// 			->whereHas('aspek_skp', function ($query) use ($bulan) {
		// 				$query->whereHas('target_skp', function ($query) use ($bulan) {
		// 					$query->where('bulan', "{$bulan}");
		// 				});
		// 			})
		// 			->where('id', $value->id)
		// 			->whereHas('aspek_skp', function ($query) use ($bulan) {
		// 				$query->whereHas('target_skp', function ($query) use ($bulan) {
		// 					$query->where('bulan', '' . $bulan . '');
		// 				});
		// 			})
		// 			->first();

		// 		if (isset($get_data_skp)) {
		// 			$result_skp[] = $get_data_skp;
		// 		}
		// 	}

		// 	// return $result_skp;



		// 	if ($total_realisasi > 0 && $total_target > 0) {
		// 		$total_kinerja = (($total_realisasi / $total_target) * 100) / count($getSkp);
		// 		$hasil_total_kinerja = ($total_kinerja / 60) * 100;
		// 	}
		// }



		if (count($list_pegawai) == 0) {
			$list_pegawai = null;
		}

		if (count($info_penilai) == 0) {
			$info_penilai = null;
		}


		$result = [
			'jumlah_skp' => count($getSkp),
			'jumlah_realisasi_skp' => $jumlah_realisasi_skp,
			'pegawai_diniai' => count($getJabatanByCurrentParent),
			'aktivitas' => $countAktivitas,
			'informasi_pegawai' => $info_pegawai,
			'informasi_penilai' => $info_penilai,
			'list_rekap_nilai' => $list_pegawai,
			'informasi_tpp' => [
				'besaran_tpp' => number_format($nilai_besaran_tpp, 2),
				'tunjangan_prestasi_kerja' => number_format($hasil_total_kinerja, 2)
			]
		];

		return $result;
	}

	public function opd_dashboard()
	{
		$count_skp = 0;
		$count_realisasi = 0;
		$count_aktifitas = 0;
		$bulan = trim(date('m'), "0") + 1;
		$data = DB::select("SELECT tb_pegawai.id, tb_pegawai.nama, tb_jabatan.nama_jabatan,tb_jabatan.id AS id_jabatan, (SELECT COUNT(*) FROM tb_skp where id_jabatan=tb_jabatan.id and month(tb_skp.created_at)=" . date('m') . ") AS jumlah_skp, (SELECT COUNT(*) FROM tb_review_realisasi_skp where id_pegawai=tb_pegawai.id and bulan=" . $bulan . ") AS jumlah_skp_terealisasi, (SELECT COUNT(*) FROM tb_aktivitas where tb_aktivitas.id_pegawai=tb_pegawai.id and month(tb_aktivitas.tanggal) = " . date('m') . ") AS jumlah_aktivitas FROM tb_pegawai INNER JOIN tb_jabatan ON tb_jabatan.id_pegawai = tb_pegawai.id where tb_pegawai.id_satuan_kerja=" . Auth::user()->pegawai['id_satuan_kerja']);


		foreach ($data as $key => $value) {
			$count_skp += $value->jumlah_skp;
			$count_realisasi += $value->jumlah_skp_terealisasi;
			$count_aktifitas += $value->jumlah_aktivitas;
		}


		$pegawaiBySatuankerja = DB::table('tb_pegawai')->select('tb_pegawai.id')->where('id_satuan_kerja', Auth::user()->pegawai['id_satuan_kerja'])->get()->count();

		// foreach ($variable as $key => $value) {
		// 	# code...
		// }

		return $result = [
			'jumlah_pegawai' => $pegawaiBySatuankerja,
			'jumlah_aktivitas' => $count_aktifitas,
			'jumlah_skp' => $count_skp,
			'jumlah_skp_terealisasi' => $count_realisasi,
		];
	}

	public function pegawai_dinilai()
	{
		$jabatanPegawai = DB::table('tb_jabatan')->select('id')->where('id_pegawai', Auth::user()->id_pegawai)->first();
		$myArray = [];
		$groupId = [];
		$groupSkpPegawai = [];
		if (isset($jabatanPegawai)) {
			$myArray = DB::table('tb_jabatan')->select('tb_jabatan.id', 'tb_jabatan.id_pegawai', 'tb_jabatan.nama_jabatan', 'tb_pegawai.nama', 'tb_pegawai.nip')->join('tb_pegawai', 'tb_jabatan.id_pegawai', '=', 'tb_pegawai.id')->where('parent_id', $jabatanPegawai->id)->get();
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
}
