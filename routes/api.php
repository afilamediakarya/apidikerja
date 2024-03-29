<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return  $request->user();
// });
Route::post('/login', [App\Http\Controllers\AuthController::class, 'login']);
Route::get('/tes/list', [App\Http\Controllers\pegawaiController::class, 'list_tes']);
Route::get('/view-rekapByUser/{start_date}/{end_date}/{id_pegawai}', [App\Http\Controllers\laporanRekapitulasiabsenController::class, 'viewrekapByUser']);
Route::get('laporan-kinerja', [App\Http\Controllers\laporanController::class, 'kinerjaView']);
Route::get('/manual', [App\Http\Controllers\skpController::class, 'manual']);
Route::get('/remove-cache', [App\Http\Controllers\AuthController::class, 'removeCache']);

// Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout']);
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/register-user', [App\Http\Controllers\AuthController::class, 'register_user']);
    Route::post('/change-password', [App\Http\Controllers\AuthController::class, 'change_password']);
    Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout']);
    Route::post('/face_id', [App\Http\Controllers\AuthController::class, 'face_id']);
    Route::get('/dashboard/{type}', [App\Http\Controllers\dashboardController::class, 'get_data']);
    Route::get('/dashboard/pegawai/level', [App\Http\Controllers\dashboardController::class, 'pegawai_dinilai']);
    Route::get('/current_user', [App\Http\Controllers\AuthController::class, 'current_user']);

    Route::prefix('admin')->group(function () {
        Route::get('/list', [App\Http\Controllers\AuthController::class, 'listUsersByOpd']);
        Route::get('/option-pegawai-satuankerja/{params}', [App\Http\Controllers\AuthController::class, 'pegawailistBySatuanKerja']);
        Route::get('/change-role/{params}', [App\Http\Controllers\AuthController::class, 'changeRoleAdmin']);
        Route::delete('/change-role/{params}', [App\Http\Controllers\AuthController::class, 'changeRolePegawai']);
    });

    Route::prefix('profile')->group(function () {
        Route::get('/personal-data', [App\Http\Controllers\ProfileController::class, 'personalData']);
        Route::get('/get-list-pendidikan', [App\Http\Controllers\ProfileController::class, 'getListPendidikan']);
        Route::get('/get-list-golongan', [App\Http\Controllers\ProfileController::class, 'getListGolongan']);
        Route::get('/get-list-unitkerja', [App\Http\Controllers\ProfileController::class, 'getListUnitkerja']);

        // pendidikan formal
        Route::get('/list-pendidikan-formal', [App\Http\Controllers\ProfileController::class, 'listPendidikanFormal']);
        Route::get('/get-pendidikan-formal/{id}', [App\Http\Controllers\ProfileController::class, 'getPendidikanFormal']);
        Route::post('/store-pendidikan-formal', [App\Http\Controllers\ProfileController::class, 'storePendidikanFormal']);
        Route::post('/update-pendidikan-formal/{id}', [App\Http\Controllers\ProfileController::class, 'updatePendidikanFormal']);
        Route::delete('/delete-pendidikan-formal/{id}', [App\Http\Controllers\ProfileController::class, 'deletePendidikanFormal']);

        // pendidikan nonformal
        Route::get('/list-pendidikan-nonformal', [App\Http\Controllers\ProfileController::class, 'listPendidikanNonFormal']);
        Route::post('/store-pendidikan-nonformal', [App\Http\Controllers\ProfileController::class, 'storePendidikanNonFormal']);
        Route::get('/get-pendidikan-nonformal/{id}', [App\Http\Controllers\ProfileController::class, 'getPendidikanNonFormal']);
        Route::post('/update-pendidikan-nonformal/{id}', [App\Http\Controllers\ProfileController::class, 'updatePendidikanNonFormal']);
        Route::delete('/delete-pendidikan-nonformal/{id}', [App\Http\Controllers\ProfileController::class, 'deletePendidikanNonFormal']);

        // kepangkatan
        Route::get('/list-kepangkatan', [App\Http\Controllers\ProfileController::class, 'listKepangkatan']);
        Route::post('/store-kepangkatan', [App\Http\Controllers\ProfileController::class, 'storeKepangkatan']);
        Route::get('/get-kepangkatan/{id}', [App\Http\Controllers\ProfileController::class, 'getKepangkatan']);
        Route::post('/update-kepangkatan/{id}', [App\Http\Controllers\ProfileController::class, 'updateKepangkatan']);
        Route::delete('/delete-kepangkatan/{id}', [App\Http\Controllers\ProfileController::class, 'deleteKepangkatan']);

        // jabatan
        Route::get('/list-jabatan', [App\Http\Controllers\ProfileController::class, 'listJabatan']);
        Route::post('/store-jabatan', [App\Http\Controllers\ProfileController::class, 'storeJabatan']);
        Route::get('/get-jabatan/{id}', [App\Http\Controllers\ProfileController::class, 'getJabatan']);
        Route::post('/update-jabatan/{id}', [App\Http\Controllers\ProfileController::class, 'updateJabatan']);
        Route::delete('/delete-jabatan/{id}', [App\Http\Controllers\ProfileController::class, 'deleteJabatan']);
    });

    Route::prefix('verifikasi')->group(function () {
        Route::get('/pendidikan-formal', [App\Http\Controllers\VerfikasiController::class, 'pendidikanFormal']);
    });

    Route::prefix('satuan')->group(function () {
        Route::get('/list', [App\Http\Controllers\satuanController::class, 'list']);
        Route::post('/store', [App\Http\Controllers\satuanController::class, 'store']);
        Route::get('/show/{params}', [App\Http\Controllers\satuanController::class, 'show']);
        Route::post('/update/{params}', [App\Http\Controllers\satuanController::class, 'update']);
        Route::delete('/delete/{params}', [App\Http\Controllers\satuanController::class, 'delete']);
    });

    Route::prefix('kelompok_jabatan')->group(function () {
        Route::get('/list', [App\Http\Controllers\KelompokjabatanController::class, 'list']);
        Route::get('/get-option/{params}', [App\Http\Controllers\KelompokjabatanController::class, 'getOption']);
        Route::post('/store', [App\Http\Controllers\KelompokjabatanController::class, 'store']);
        Route::get('/show/{params}', [App\Http\Controllers\KelompokjabatanController::class, 'show']);
        Route::post('/update/{params}', [App\Http\Controllers\KelompokjabatanController::class, 'update']);
        Route::delete('/delete/{params}', [App\Http\Controllers\KelompokjabatanController::class, 'delete']);
    });

    Route::prefix('master_aktivitas')->group(function () {
        Route::get('/list', [App\Http\Controllers\masterAktivitasController::class, 'list']);
        Route::get('/option', [App\Http\Controllers\masterAktivitasController::class, 'option']);
        Route::post('/store', [App\Http\Controllers\masterAktivitasController::class, 'store']);
        Route::get('/show/{params}', [App\Http\Controllers\masterAktivitasController::class, 'show']);
        Route::post('/update/{params}', [App\Http\Controllers\masterAktivitasController::class, 'update']);
        Route::delete('/delete/{params}', [App\Http\Controllers\masterAktivitasController::class, 'delete']);
    });

    Route::prefix('harilibur')->group(function () {
        Route::get('/list', [App\Http\Controllers\hariliburController::class, 'list']);
        Route::post('/store', [App\Http\Controllers\hariliburController::class, 'store']);
        Route::get('/show/{params}', [App\Http\Controllers\hariliburController::class, 'show']);
        Route::post('/update/{params}', [App\Http\Controllers\hariliburController::class, 'update']);
        Route::delete('/delete/{params}', [App\Http\Controllers\hariliburController::class, 'delete']);
    });


    Route::prefix('satuan_kerja')->group(function () {
        Route::get('/list', [App\Http\Controllers\satuanKerjaController::class, 'list']);
        Route::get('/byAdminOpd', [App\Http\Controllers\satuanKerjaController::class, 'listByAdminOpd']);
        Route::post('/store', [App\Http\Controllers\satuanKerjaController::class, 'store']);
        Route::get('/show/{params}', [App\Http\Controllers\satuanKerjaController::class, 'show']);
        Route::post('/update/{params}', [App\Http\Controllers\satuanKerjaController::class, 'update']);
        Route::delete('/delete/{params}', [App\Http\Controllers\satuanKerjaController::class, 'delete']);
    });

    Route::prefix('pegawai')->group(function () {
        Route::get('/list', [App\Http\Controllers\pegawaiController::class, 'list']);
        Route::get('/BySatuanKerja/{params}', [App\Http\Controllers\pegawaiController::class, 'pegawaiBySatuanKerja']);
        Route::get('/listBySatuanKerja', [App\Http\Controllers\pegawaiController::class, 'listPegawaiBySatuanKerja']);
        Route::post('/store', [App\Http\Controllers\pegawaiController::class, 'store']);
        Route::get('/show/{params}', [App\Http\Controllers\pegawaiController::class, 'show']);
        Route::post('/update/{params}', [App\Http\Controllers\pegawaiController::class, 'update']);
        Route::delete('/delete/{params}', [App\Http\Controllers\pegawaiController::class, 'delete']);
        Route::get('/get-option-agama', [App\Http\Controllers\pegawaiController::class, 'optionAgama']);
        Route::get('/get-option-status-kawin', [App\Http\Controllers\pegawaiController::class, 'optionStatusKawin']);
        Route::get('/get-option-pendidikan-terakhir', [App\Http\Controllers\pegawaiController::class, 'pendidikanTerakhir']);
        Route::get('/get-option-pangkat-golongan', [App\Http\Controllers\pegawaiController::class, 'optionGolongan']);
        Route::get('/get-option-status-pegawai', [App\Http\Controllers\pegawaiController::class, 'optionStatusPegawai']);
        Route::get('/get-option-status-eselon', [App\Http\Controllers\pegawaiController::class, 'optionEselon']);
        Route::get('/tesScheduling', [App\Http\Controllers\pegawaiController::class, 'tesScheduling']);
        Route::post('/reset-password/{id}', [App\Http\Controllers\pegawaiController::class, 'reset_password']);
    });

    Route::prefix('jadwal')->group(function () {
        Route::get('/list', [App\Http\Controllers\jadwalController::class, 'list']);
        Route::post('/store', [App\Http\Controllers\jadwalController::class, 'store']);
        Route::get('/show/{params}', [App\Http\Controllers\jadwalController::class, 'show']);
        Route::post('/update/{params}', [App\Http\Controllers\jadwalController::class, 'update']);
        Route::delete('/delete/{params}', [App\Http\Controllers\jadwalController::class, 'delete']);
        Route::get('/option-tahapan', [App\Http\Controllers\jadwalController::class, 'optionTahapan']);
        Route::get('/option-sub-tahapan/{params}', [App\Http\Controllers\jadwalController::class, 'optionSubTahapan']);
        Route::get('/check_jadwal', [App\Http\Controllers\jadwalController::class, 'check_jadwal']);

    });

    Route::prefix('profil_daerah')->group(function () {
        Route::get('/list', [App\Http\Controllers\profilDaerahController::class, 'list']);
        Route::post('/store', [App\Http\Controllers\profilDaerahController::class, 'store']);
        Route::get('/show/{params}', [App\Http\Controllers\profilDaerahController::class, 'show']);
        Route::post('/update/{params}', [App\Http\Controllers\profilDaerahController::class, 'update']);
        Route::delete('/delete/{params}', [App\Http\Controllers\profilDaerahController::class, 'delete']);
    });

    Route::prefix('informasi')->group(function () {
        Route::get('/list', [App\Http\Controllers\informasiController::class, 'list']);
        Route::post('/store', [App\Http\Controllers\informasiController::class, 'store']);
        Route::get('/show/{params}', [App\Http\Controllers\informasiController::class, 'show']);
        Route::post('/update/{params}', [App\Http\Controllers\informasiController::class, 'update']);
        Route::delete('/delete/{params}', [App\Http\Controllers\informasiController::class, 'delete']);
    });

    Route::prefix('bidang')->group(function () {
        Route::get('/list', [App\Http\Controllers\bidangController::class, 'list']);
        Route::post('/store', [App\Http\Controllers\bidangController::class, 'store']);
        Route::get('/show/{params}', [App\Http\Controllers\bidangController::class, 'show']);
        Route::post('/update/{params}', [App\Http\Controllers\bidangController::class, 'update']);
        Route::delete('/delete/{params}', [App\Http\Controllers\bidangController::class, 'delete']);
    });

    Route::prefix('kegiatan')->group(function () {
        Route::get('/list', [App\Http\Controllers\kegiatanController::class, 'list']);
        Route::post('/store', [App\Http\Controllers\kegiatanController::class, 'store']);
        Route::get('/show/{params}', [App\Http\Controllers\kegiatanController::class, 'show']);
        Route::post('/update/{params}', [App\Http\Controllers\kegiatanController::class, 'update']);
        Route::delete('/delete/{params}', [App\Http\Controllers\kegiatanController::class, 'delete']);
    });

    Route::prefix('skp')->group(function () {
        Route::get('/list/{params}', [App\Http\Controllers\skpController::class, 'list']);
        Route::post('/store', [App\Http\Controllers\skpController::class, 'store']);
        Route::post('/store-bulanan', [App\Http\Controllers\skpController::class, 'store_bulanan']);
        Route::post('/update-bulanan/{params}', [App\Http\Controllers\skpController::class, 'update_bulanan']);
        Route::get('/show/{params}', [App\Http\Controllers\skpController::class, 'show']);
        Route::post('/update/{params}', [App\Http\Controllers\skpController::class, 'update']);
        Route::delete('/delete/{params}', [App\Http\Controllers\skpController::class, 'destroy']);
        Route::get('/get-option-satuan', [App\Http\Controllers\skpController::class, 'satuan']);
        Route::get('/get-option-sasaran-kinerja', [App\Http\Controllers\skpController::class, 'optionSkp']);
    });

    Route::prefix('aktivitas')->group(function () {
        Route::get('/list', [App\Http\Controllers\aktivitasController::class, 'list']);
        Route::get('/listByDate', [App\Http\Controllers\aktivitasController::class, 'listByDate']);
        Route::get('/list-by-user', [App\Http\Controllers\aktivitasController::class, 'listByUser']);
        Route::get('/list-by-review', [App\Http\Controllers\aktivitasController::class, 'listByReview']);
        Route::post('/store', [App\Http\Controllers\aktivitasController::class, 'store']);
        Route::get('/show/{params}', [App\Http\Controllers\aktivitasController::class, 'show']);
        Route::post('/update/{params}', [App\Http\Controllers\aktivitasController::class, 'update']);
        Route::delete('/delete/{params}', [App\Http\Controllers\aktivitasController::class, 'delete']);
        Route::get('/get-option-sasaran-kinerja', [App\Http\Controllers\aktivitasController::class, 'optionSkp']);
        Route::get('/checkMenitKinerja/{params}', [App\Http\Controllers\aktivitasController::class, 'checkMenitKinerja']);
        Route::get('/review_aktivitas_list', [App\Http\Controllers\aktivitasController::class, 'review_aktivitas_list']);
        Route::post('/review_aktivitas', [App\Http\Controllers\aktivitasController::class, 'review_aktivitas_post']);
        // Route::get('/review-aktivitas/{params}', [App\Http\Controllers\aktivitasController::class, 'review_aktivitas_byPegawai']);
    });

    Route::prefix('realisasi_skp')->group(function () {
        Route::get('/list', [App\Http\Controllers\realisasiController::class, 'list']);
        Route::post('/store', [App\Http\Controllers\realisasiController::class, 'store']);
        Route::get('/show/{params}', [App\Http\Controllers\realisasiController::class, 'show']);
        Route::get('/realisasiKuantitas/{params}/{id_skp}', [App\Http\Controllers\realisasiController::class, 'realisasiKuantitas']);
        Route::post('/update/{params}', [App\Http\Controllers\realisasiController::class, 'update']);
        Route::delete('/delete/{params}', [App\Http\Controllers\realisasiController::class, 'delete']);
    });

    Route::prefix('atasan')->group(function () {
        Route::get('/list', [App\Http\Controllers\atasanController::class, 'list']);
        Route::post('/store', [App\Http\Controllers\atasanController::class, 'store']);
        Route::get('/show/{params}', [App\Http\Controllers\atasanController::class, 'show']);
        Route::post('/update/{params}', [App\Http\Controllers\atasanController::class, 'update']);
        Route::delete('/delete/{params}', [App\Http\Controllers\atasanController::class, 'delete']);
        Route::get('/option-atasan', [App\Http\Controllers\atasanController::class, 'option_atasan']);
    });

    Route::prefix('absen')->group(function () {
        Route::get('/list', [App\Http\Controllers\absenController::class, 'list']);
        Route::get('/check-absen-today', [App\Http\Controllers\absenController::class, 'checkAbsenToday']);
        Route::get('/check-absen-by-date', [App\Http\Controllers\absenController::class, 'checkAbsenbyDate']);
        Route::get('/list-filter-absen', [App\Http\Controllers\absenController::class, 'list_filter_absen']);
        Route::get('/get-time-now', [App\Http\Controllers\absenController::class, 'getTime']);
        Route::post('/store', [App\Http\Controllers\absenController::class, 'store']);
        Route::get('/show/{pegawai}/{tanggal}/{valid}', [App\Http\Controllers\absenController::class, 'show']);
        Route::post('/update/{params}', [App\Http\Controllers\absenController::class, 'update']);
        Route::delete('/delete/{params}', [App\Http\Controllers\absenController::class, 'delete']);
        Route::get('/check-absen', [App\Http\Controllers\absenController::class, 'checkAbsen']);
        Route::get('/check-absen-admin/{id_pegawai}/{tanggal}', [App\Http\Controllers\absenController::class, 'absenCheckAdmin']);
        Route::post('/change-validation', [App\Http\Controllers\absenController::class, 'change_validation']);

        
    });

    Route::prefix('review_skp')->group(function () {
        Route::get('/list', [App\Http\Controllers\reviewController::class, 'list']);
        Route::post('/store', [App\Http\Controllers\reviewController::class, 'store']);
        Route::get('/skpbyId/{type}', [App\Http\Controllers\reviewController::class, 'skpbyId']);
        Route::get('/group_list', [App\Http\Controllers\reviewController::class, 'group_list']);
    });

    Route::prefix('review_realisasi')->group(function () {
        Route::get('/list', [App\Http\Controllers\reviewRealisasiSkpController::class, 'list']);
        Route::post('/store', [App\Http\Controllers\reviewRealisasiSkpController::class, 'store']);
        Route::get('/skpbyId/{type}', [App\Http\Controllers\reviewRealisasiSkpController::class, 'skpbyId']);
    });

    Route::prefix('perilaku')->group(function () {
        Route::get('/list', [App\Http\Controllers\perilakuController::class, 'list']);
        Route::post('/store', [App\Http\Controllers\perilakuController::class, 'store']);
        Route::get('/show/{params}', [App\Http\Controllers\perilakuController::class, 'show']);
        Route::post('/update/{params}', [App\Http\Controllers\perilakuController::class, 'update']);
        Route::delete('/delete/{params}', [App\Http\Controllers\perilakuController::class, 'delete']);
    });

    Route::prefix('faq')->group(function () {
        Route::get('/list', [App\Http\Controllers\faqController::class, 'list']);
        Route::post('/store', [App\Http\Controllers\faqController::class, 'store']);
        Route::get('/show/{params}', [App\Http\Controllers\faqController::class, 'show']);
        Route::post('/update/{params}', [App\Http\Controllers\faqController::class, 'update']);
        Route::delete('/delete/{params}', [App\Http\Controllers\faqController::class, 'delete']);
    });

    Route::prefix('kelas_jabatan')->group(function () {
        Route::get('/list', [App\Http\Controllers\kelasJabatanController::class, 'list']);
        Route::post('/store', [App\Http\Controllers\kelasJabatanController::class, 'store']);
        Route::get('/show/{params}', [App\Http\Controllers\kelasJabatanController::class, 'show']);
        Route::post('/update/{params}', [App\Http\Controllers\kelasJabatanController::class, 'update']);
        Route::delete('/delete/{params}', [App\Http\Controllers\kelasJabatanController::class, 'delete']);
        Route::get('/get-option-kelas-jabatan', [App\Http\Controllers\kelasJabatanController::class, 'optionKelasJabatan']);
    });

    Route::prefix('jabatan')->group(function () {
        Route::get('/list', [App\Http\Controllers\jabatanController::class, 'list']);
        Route::post('/store', [App\Http\Controllers\jabatanController::class, 'store']);
        Route::get('/show/{params}', [App\Http\Controllers\jabatanController::class, 'show']);
        Route::post('/update/{params}', [App\Http\Controllers\jabatanController::class, 'update']);
        Route::delete('/delete/{params}', [App\Http\Controllers\jabatanController::class, 'delete']);
        Route::get('/get-option-jabatan-atasan/{params}/{id_satuan_kerja}', [App\Http\Controllers\jabatanController::class, 'jabatanAtasan']);
        Route::get('/pegawaiBySatuanKerja', [App\Http\Controllers\jabatanController::class, 'getPegawaiBySatuanKerja']);
        Route::get('/get-option-jenis-jabatan', [App\Http\Controllers\jabatanController::class, 'getOptionJenisJabatan']);
        Route::get('/get-option-parent/{params}', [App\Http\Controllers\jabatanController::class, 'getParent']);
    });

    Route::prefix('laporan-rekapitulasi-absen')->group(function () {
        Route::get('/rekapByUser/{start_date}/{end_date}', [App\Http\Controllers\laporanRekapitulasiabsenController::class, 'rekapByUser']);
        Route::get('/rekapByOpd/{start_date}/{end_date}/{satuan_kerja}', [App\Http\Controllers\laporanRekapitulasiabsenController::class, 'rekapByAdminOpd']);
    });

    // rekap tpp
    Route::prefix('laporan-rekapitulasi-tpp')->group(function () {
        Route::get('/admin-opd', [App\Http\Controllers\laporanRekapitulasiTppController::class, 'rekapTpp']);
    });

    Route::prefix('laporan')->group(function () {
        // Route::get('/skp/{level}', [App\Http\Controllers\laporanController::class, 'laporanSkp']);
        Route::get('/skp/cekLevel/{id_pegawai}', [App\Http\Controllers\laporanController::class, 'cekLevel']);
        Route::get('/skp/rekapitulasi/{bulan}', [App\Http\Controllers\laporanController::class, 'laporanRekapitulasiSkp']);
        Route::get('/skp/{level}/{bulan}/{id_pegawai}', [App\Http\Controllers\laporanController::class, 'laporanSkp']);
        Route::get('/kinerja', [App\Http\Controllers\laporanController::class, 'kinerja']);
        Route::get('/kinerjaByOpd', [App\Http\Controllers\laporanController::class, 'kinerjaByOpd']);
    });

    Route::prefix('bankom')->group(function () {
        Route::get('/list', [App\Http\Controllers\bankomController::class, 'list']);
        Route::get('/laporan/{type}/{satker}/{year}/{id_pegawai}', [App\Http\Controllers\bankomController::class, 'laporan']);
        Route::post('/store', [App\Http\Controllers\bankomController::class, 'store']);
        Route::get('/show/{params}', [App\Http\Controllers\bankomController::class, 'show']);
        Route::post('/update/{params}', [App\Http\Controllers\bankomController::class, 'update']);
        Route::delete('/delete/{params}', [App\Http\Controllers\bankomController::class, 'delete']);
    });

    Route::prefix('lokasi')->group(function () {
        Route::get('/list', [App\Http\Controllers\lokasiController::class, 'list']);
        Route::get('/optionLokasi', [App\Http\Controllers\lokasiController::class, 'optionLokasi']);
        Route::post('/store', [App\Http\Controllers\lokasiController::class, 'store']);
        Route::get('/show/{params}', [App\Http\Controllers\lokasiController::class, 'show']);
        Route::post('/update/{params}', [App\Http\Controllers\lokasiController::class, 'update']);
        Route::delete('/delete/{params}', [App\Http\Controllers\lokasiController::class, 'delete']);
    });
});
