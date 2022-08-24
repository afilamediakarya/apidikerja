<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\satuan_kerja;
use App\Models\pegawai;
use Validator;
use Auth;

class satuanKerjaController extends Controller
{
    public function list()
    {
        $data = satuan_kerja::latest()->get();

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

    public function listByAdminOpd()
    {
        // return "ok";
        $pegawai = pegawai::where('id', Auth::user()->id_pegawai)->first();
        $data = satuan_kerja::where('id', $pegawai->id_satuan_kerja)->latest()->get();

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
        // return $request->all();
        $validator = Validator::make($request->all(), [
            'nama_satuan_kerja' => 'required|string|unique:tb_satuan_kerja',
            'lat_location' => 'required|string',
            'inisial_satuan_kerja' => 'required|string',
            'long_location' => 'required|string',
            'status_kepala' => 'required|string',
            'tahun' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $data = new satuan_kerja();
        $data->kode_satuan_kerja = '001';
        $data->nama_satuan_kerja = $request->nama_satuan_kerja;
        $data->inisial_satuan_kerja = $request->inisial_satuan_kerja;
        $data->lat_location = $request->lat_location;
        $data->long_location = $request->long_location;
        $data->status_kepala = $request->status_kepala;
        $data->tahun = $request->tahun;
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

    public function show($params)
    {
        $data = satuan_kerja::where('id', $params)->first();

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
        $validator = Validator::make($request->all(), [
            'nama_satuan_kerja' => 'required|string',
            'lat_location' => 'required|string',
            'long_location' => 'required|string',
            'status_kepala' => 'required|string',
            'inisial_satuan_kerja' => 'required|string',
            'tahun' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $data = satuan_kerja::where('id', $params)->first();
        $data->kode_satuan_kerja = '001';
        $data->nama_satuan_kerja = $request->nama_satuan_kerja;
        $data->inisial_satuan_kerja = $request->inisial_satuan_kerja;
        $data->lat_location = $request->lat_location;
        $data->long_location = $request->long_location;
        $data->status_kepala = $request->status_kepala;
        $data->tahun = $request->tahun;
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
        $data = satuan_kerja::where('id', $params)->first();
        $data->delete();

        if ($data) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
            ]);
        } else {
            return response()->json([
                'message' => 'Failed',
                'status' => false
            ]);
        }
    }
}
