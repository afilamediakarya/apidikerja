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
use Illuminate\Support\Facades\Auth as FacadesAuth;

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
		$list_pegawai = [];
		$result = [];
		$temporary = [];
		$getJabatanByCurrentParent = [];

		$getJabatanPegawai = DB::table('tb_jabatan')->where('id_pegawai', Auth::user()->id_pegawai)->first();
		if (isset($getJabatanPegawai)) {
			$getJabatanAtasan = DB::table('tb_jabatan')->where('id', $getJabatanPegawai->parent_id)->first();
			$getJabatanByCurrentParent = jabatan::where('parent_id', $getJabatanPegawai->id)->get();
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


		// INFO SKP
		// // $data_skp = DB::table('tb_skp')
		// 	->select('tb_skp.id')
		// 	->join('tb_jabatan', 'tb_skp.id_jabatan', 'tb_jabatan.id')
		// 	->join('tb_pegawai', 'tb_jabatan.id_pegawai', '=', 'tb_pegawai.id')
		// 	->where('tb_jabatan.id_pegawai', Auth::user()->id_pegawai)
		// 	->get();
		$bulan = request('bulan');

		$get_skp =
			skp::with(['aspek_skp' => function ($query) use ($bulan) {
				$query->with(['target_skp' => function ($select) use ($bulan) {
					$select->where('bulan', "{$bulan}");
				}])
					->with(['realisasi_skp' => function ($select) use ($bulan) {
						$select->where('bulan', "{$bulan}");
					}]);
			}])
			->whereHas('aspek_skp', function ($query) use ($bulan) {
				$query->whereHas('target_skp', function ($query) use ($bulan) {
					$query->where('bulan', "{$bulan}");
				});
			})
			->where('id_jabatan', $getJabatanPegawai->id)
			->whereHas('aspek_skp', function ($query) use ($bulan) {
				$query->whereHas('target_skp', function ($query) use ($bulan) {
					$query->where('bulan', '' . $bulan . '');
				});
			})
			->orderBy('jenis')
			->get();


		$jumlah_skp = count($get_skp);;



		// INFO REALISASI SKP
		$jumlah_realisasi_skp = 0;
		foreach ($get_skp as $index => $value) {

			$realisasi = DB::table('tb_review_realisasi_skp')->where('id_skp', $value->id)
				->where('bulan', request('bulan'))
				// ->where('kesesuaian', 'ya')
				->first();

			if (!is_null($realisasi) && $realisasi->kesesuaian == "ya") {
				$jumlah_realisasi_skp += 1;
			}
		}


		// INFO TPP
		$cek = [];
		$total_realisasi = 0;
		$total_target = 0;
		$total_kinerja = 0;
		$total_kehadiran = 0;

		$nilai_besaran_tpp = 0;
		$nilai_besaran_tpp = $get_pegawai['nilai_jabatan'];

		// INFO TPP 
		// KINERJA
		if (isset($get_skp)) {

			$nilai_utama = 0;
			$nilai_tambahan = 0;

			$total_utama = 0;
			$total_tambahan = 0;

			$data_utama = 0;
			$index_data = 0;

			$jumlah_data = 0;
			$sum_nilai_iki = 0;

			if ($info_pegawai['level_jabatan'] == 1 || $info_pegawai['level_jabatan'] == 2) {
				foreach ($get_skp as $index => $value) {


					// cek if isset skp_utama
					if ($value->jenis == "utama") {

						$index_data++;
						$data_utama++;

						$sum_capaian = 0;
						foreach ($value->aspek_skp as $key => $val) {

							foreach ($val['target_skp'] as $mk => $rr) {
								$kategori_ = '';
								if ($rr['bulan'] ==  $bulan) {

									$single_rate = ($val['realisasi_skp'][$mk]['realisasi_bulanan'] / $rr['target']) * 100;

									if ($single_rate > 110) {
										$nilai_iki = 110 + ((120 - 110) / (110 - 101)) * (110 - 101);
									} elseif ($single_rate >= 101 && $single_rate <= 110) {
										$nilai_iki = 110 + ((120 - 110) / (110 - 101)) * ($single_rate - 101);
									} elseif ($single_rate == 100) {
										$nilai_iki = 109;
									} elseif ($single_rate >= 80 && $single_rate <= 99) {
										$nilai_iki = 70 + ((89 - 70) / (99 - 80)) * ($single_rate - 80);
									} elseif ($single_rate >= 60 && $single_rate <= 79) {
										$nilai_iki = 50 + ((69 - 50) / (79 - 60)) * ($single_rate - 60);
									} elseif ($single_rate >= 0 && $single_rate <= 79) {
										$nilai_iki = (49 / 59) * $single_rate;
									}

									$sum_nilai_iki += $nilai_iki;
									$jumlah_data++;
								}
							}
						}


						// cek if total_utama & data_utama != 0
						if ($sum_nilai_iki != 0 && $jumlah_data != 0) {
							$nilai_utama = round($sum_nilai_iki / $jumlah_data, 1);
						} else {
							$nilai_utama = 0;
						}
					} elseif ($value->jenis == "tambahan") {

						$sum_capaian = 0;
						foreach ($value->aspek_skp as $k => $v) {

							foreach ($v['target_skp'] as $mk => $rr) {
								$kategori_ = '';
								if ($rr['bulan'] ==  $bulan) {

									$single_rate = ($v['realisasi_skp'][$mk]['realisasi_bulanan'] / $rr['target']) * 100;

									if ($single_rate > 110) {
										$nilai_iki = 110 + ((120 - 110) / (110 - 101)) * (110 - 101);
									} elseif ($single_rate >= 101 && $single_rate <= 110) {
										$nilai_iki = 110 + ((120 - 110) / (110 - 101)) * ($single_rate - 101);
									} elseif ($single_rate == 100) {
										$nilai_iki = 109;
									} elseif ($single_rate >= 80 && $single_rate <= 99) {
										$nilai_iki = 70 + ((89 - 70) / (99 - 80)) * ($single_rate - 80);
									} elseif ($single_rate >= 60 && $single_rate <= 79) {
										$nilai_iki = 50 + ((69 - 50) / (79 - 60)) * ($single_rate - 60);
									} elseif ($single_rate >= 0 && $single_rate <= 79) {
										$nilai_iki = (49 / 59) * $single_rate;
									}

									if ($nilai_iki > 110) {
										$total_tambahan += 2.4;
									} elseif ($nilai_iki >= 101 && $nilai_iki <= 110) {
										$total_tambahan += 1.6;
									} elseif ($nilai_iki == 100) {
										$total_tambahan += 1.0;
									} elseif ($nilai_iki >= 80 && $nilai_iki <= 99) {
										$total_tambahan += 0.5;
									} elseif ($nilai_iki >= 60 && $nilai_iki <= 79) {
										$total_tambahan += 0.3;
									} elseif ($nilai_iki >= 0 && $nilai_iki <= 79) {
										$total_tambahan += 0.1;
									}
								}
							}
						}
					}
				}

				if ($sum_nilai_iki != 0 && $jumlah_data != 0) {
					$nilai_utama = round($sum_nilai_iki / $jumlah_data, 1);
				} else {
					$nilai_utama = 0;
				}

				$nilai_tambahan = $total_tambahan;
			} else {
				foreach ($get_skp as $index => $value) {

					// cek if isset skp_utama
					if ($value->jenis == "utama") {

						$index_data++;
						$data_utama++;

						$sum_capaian = 0;
						foreach ($value->aspek_skp as $key => $val) {

							foreach ($val['target_skp'] as $mk => $rr) {

								$kategori_ = '';
								if ($rr['bulan'] ==  $bulan) {
									// set capaian_iki based realisasi / target
									$capaian_iki = ($val['realisasi_skp'][$mk]['realisasi_bulanan'] / $rr['target']) * 100;

									// set nilai_iki based capaian_iki
									if ($capaian_iki >= 101) {
										$nilai_iki = 16;
									} elseif ($capaian_iki == 100) {
										$nilai_iki = 13;
									} elseif ($capaian_iki >= 80 && $capaian_iki <= 99) {
										$nilai_iki = 8;
									} elseif ($capaian_iki >= 60 && $capaian_iki <= 79) {
										$nilai_iki = 3;
									} elseif ($capaian_iki >= 0 && $capaian_iki <= 79) {
										$nilai_iki = 1;
									}
									$sum_capaian += $nilai_iki;
								}
							}
						}

						// set total_utama based sum_capaian
						if ($sum_capaian > 42) {
							$total_utama += 120;
						} elseif ($sum_capaian >= 34) {
							$total_utama += 100;
						} elseif ($sum_capaian >= 19) {
							$total_utama += 80;
						} elseif ($sum_capaian >= 7) {
							$total_utama += 60;
						} elseif ($sum_capaian >= 3) {
							$total_utama += 25;
						} elseif ($sum_capaian >= 0) {
							$total_utama += 25;
						}


						// cek if total_utama & data_utama != 0
						if ($total_utama != 0 && $data_utama != 0) {
							$nilai_utama = round($total_utama / $data_utama, 1);
						} else {
							$nilai_utama = 0;
						}
					} elseif ($value->jenis == "tambahan") {

						$sum_capaian = 0;
						foreach ($value->aspek_skp as $k => $v) {

							foreach ($v['target_skp'] as $mk => $rr) {
								if ($rr['bulan'] ==  $bulan) {

									$capaian_iki = ($v['realisasi_skp'][$mk]['realisasi_bulanan'] / $rr['target']) * 100;

									if ($capaian_iki >= 101) {
										$nilai_iki = 16;
									} elseif ($capaian_iki == 100) {
										$nilai_iki = 13;
									} elseif ($capaian_iki >= 80 && $capaian_iki <= 99) {
										$nilai_iki = 8;
									} elseif ($capaian_iki >= 60 && $capaian_iki <= 79) {
										$nilai_iki = 3;
									} elseif ($capaian_iki >= 0 && $capaian_iki <= 79) {
										$nilai_iki = 1;
									}
									$sum_capaian += $nilai_iki;
								}
							}
						}


						if ($sum_capaian >= 42) {
							$total_tambahan += 2.4;
						} elseif ($sum_capaian >= 34) {
							$total_tambahan += 1.6;
						} elseif ($sum_capaian >= 19) {
							$total_tambahan += 1;
						} elseif ($sum_capaian >= 7) {
							$total_tambahan += 0.5;
						} elseif ($sum_capaian >= 3) {
							$total_tambahan += 0.1;
						} elseif ($sum_capaian >= 0) {
							$total_tambahan += 0.1;
						}
					}
				}

				// cek if total_utama & data_utama != 0
				if ($total_utama != 0 && $data_utama != 0) {
					$nilai_utama = round($total_utama / $data_utama, 1);
				} else {
					$nilai_utama = 0;
				}

				$nilai_tambahan = $total_tambahan;
			}
		}

		$total_kinerja = round($nilai_utama + $nilai_tambahan, 1);

		// INFO TPP
		// KEHADIRAN
		$current_date = date("Y-{$bulan}-d");
		// First day of the month.
		$first_date =  date("Y-m-01", strtotime($current_date));
		// Last day of the month.
		$last_date =  date('Y-m-t', strtotime($current_date));
		$url = env('APP_URL');
		$request_data_absen = Request::create($url . "/view-rekapByUser/{$first_date}/{$last_date}/" . Auth::user()->id_pegawai, 'GET');
		$response_data_absen = \Illuminate\Support\Facades\Route::dispatch($request_data_absen);

		$total_kehadiran = $response_data_absen->getData()->data->persentase_pemotongan;


		// // AKTIVITAS
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



		if (count($list_pegawai) == 0) {
			$list_pegawai = null;
		}

		if (count($info_penilai) == 0) {
			$info_penilai = null;
		}


		$result = [
			// 'jumlah_skp' => $jumlah_skp,
			'jumlah_skp' => 0,
			'jumlah_realisasi_skp' => $jumlah_realisasi_skp,
			'pegawai_diniai' => count($getJabatanByCurrentParent),
			'aktivitas' => $countAktivitas,
			'informasi_pegawai' => $info_pegawai,
			'informasi_penilai' => $info_penilai,
			'list_rekap_nilai' => $list_pegawai,
			'informasi_tpp' => [
				'besaran_tpp' => number_format($nilai_besaran_tpp, 2),
				'tunjangan_prestasi_kerja' => $total_kinerja,
				'tunjangan_prestasi_kehadiran' => $total_kehadiran,
			],
			'bulan' => request('bulan')
		];

		return $result;
	}

	public function opd_dashboard()
	// 0
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
