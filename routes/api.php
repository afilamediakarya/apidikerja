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
// Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout']);
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/register-user', [App\Http\Controllers\AuthController::class, 'register_user']);  
    Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout']);

    Route::prefix('satuan')->group(function () {
        Route::get('/list', [App\Http\Controllers\satuanController::class, 'list']);  
        Route::post('/store', [App\Http\Controllers\satuanController::class, 'store']);  
        Route::get('/show/{params}', [App\Http\Controllers\satuanController::class, 'show']);  
        Route::post('/update/{params}', [App\Http\Controllers\satuanController::class, 'update']);  
        Route::delete('/delete/{params}', [App\Http\Controllers\satuanController::class, 'delete']);  
    });

    Route::prefix('satuan_kerja')->group(function () {
        Route::get('/list', [App\Http\Controllers\satuanKerjaController::class, 'list']);  
        Route::post('/store', [App\Http\Controllers\satuanKerjaController::class, 'store']);  
        Route::get('/show/{params}', [App\Http\Controllers\satuanKerjaController::class, 'show']);  
        Route::post('/update/{params}', [App\Http\Controllers\satuanKerjaController::class, 'update']);  
        Route::delete('/delete/{params}', [App\Http\Controllers\satuanKerjaController::class, 'delete']);  
    });

    Route::prefix('pegawai')->group(function () {
        Route::get('/list', [App\Http\Controllers\pegawaiController::class, 'list']);  
        Route::post('/store', [App\Http\Controllers\pegawaiController::class, 'store']);  
        Route::get('/show/{params}', [App\Http\Controllers\pegawaiController::class, 'show']);  
        Route::post('/update/{params}', [App\Http\Controllers\pegawaiController::class, 'update']);  
        Route::delete('/delete/{params}', [App\Http\Controllers\pegawaiController::class, 'delete']);  
    });

    Route::prefix('jadwal')->group(function () {
        Route::get('/list', [App\Http\Controllers\jadwalController::class, 'list']);  
        Route::post('/store', [App\Http\Controllers\jadwalController::class, 'store']);  
        Route::get('/show/{params}', [App\Http\Controllers\jadwalController::class, 'show']);  
        Route::post('/update/{params}', [App\Http\Controllers\jadwalController::class, 'update']);  
        Route::delete('/delete/{params}', [App\Http\Controllers\jadwalController::class, 'delete']);  
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
        Route::get('/list', [App\Http\Controllers\skpController::class, 'list']);  
        Route::post('/store', [App\Http\Controllers\skpController::class, 'store']);  
        Route::get('/show/{params}', [App\Http\Controllers\skpController::class, 'show']);  
        Route::post('/update/{params}', [App\Http\Controllers\skpController::class, 'update']);  
        Route::delete('/delete/{params}', [App\Http\Controllers\skpController::class, 'delete']);  
    });

    Route::prefix('aktivitas')->group(function () {
        Route::get('/list', [App\Http\Controllers\aktivitasController::class, 'list']);  
        Route::post('/store', [App\Http\Controllers\aktivitasController::class, 'store']);  
        Route::get('/show/{params}', [App\Http\Controllers\aktivitasController::class, 'show']);  
        Route::post('/update/{params}', [App\Http\Controllers\aktivitasController::class, 'update']);  
        Route::delete('/delete/{params}', [App\Http\Controllers\aktivitasController::class, 'delete']);  
    });

    Route::prefix('realisasi_skp')->group(function () {
        Route::get('/list', [App\Http\Controllers\realisasiController::class, 'list']);  
        Route::post('/store', [App\Http\Controllers\realisasiController::class, 'store']);  
        Route::get('/show/{params}', [App\Http\Controllers\realisasiController::class, 'show']);  
        Route::post('/update/{params}', [App\Http\Controllers\realisasiController::class, 'update']);  
        Route::delete('/delete/{params}', [App\Http\Controllers\realisasiController::class, 'delete']);  
    });

    Route::prefix('atasan')->group(function () {
        Route::get('/list', [App\Http\Controllers\atasanController::class, 'list']);  
        Route::post('/store', [App\Http\Controllers\atasanController::class, 'store']);  
        Route::get('/show/{params}', [App\Http\Controllers\atasanController::class, 'show']);  
        Route::post('/update/{params}', [App\Http\Controllers\atasanController::class, 'update']);  
        Route::delete('/delete/{params}', [App\Http\Controllers\atasanController::class, 'delete']);  
    });

    Route::prefix('absen')->group(function () {
        Route::get('/list', [App\Http\Controllers\absenController::class, 'list']);  
        Route::post('/store', [App\Http\Controllers\absenController::class, 'store']);  
        Route::get('/show/{params}', [App\Http\Controllers\absenController::class, 'show']);  
        Route::post('/update/{params}', [App\Http\Controllers\absenController::class, 'update']);  
        Route::delete('/delete/{params}', [App\Http\Controllers\absenController::class, 'delete']);  
    });

    Route::prefix('review_skp')->group(function () {
        Route::post('/store', [App\Http\Controllers\reviewController::class, 'store']);    
    });

    Route::prefix('review_realisasi')->group(function () {
        Route::post('/store', [App\Http\Controllers\reviewRealisasiSkpController::class, 'store']);    
    });

});




