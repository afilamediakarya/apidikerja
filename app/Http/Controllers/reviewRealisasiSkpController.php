<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\review_realisasi_skp;
use Validator;
class reviewRealisasiSkpController extends Controller
{
    public function store(Request $request){
        // return $request->all();
        $validator = Validator::make($request->all(),[
            'id_skp' => 'required|array',
            'keterangan' => 'required|array',
            'kesesuaian' => 'required|array',
            'bulan' => 'required|array',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        for ($i=0; $i < count($request->id_skp); $i++) { 
            $data = review_realisasi_skp::where('id_skp',$request->id_skp[$i])->where('bulan',$request->bulan[$i])->first();
            $data->keterangan = $request->keterangan[$i];
            $data->kesesuaian = $request->kesesuaian[$i];
            $data->save();
        }

        // return $cek;


        if ($data) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $data
            ]);
        }else{
            return response()->json([
                'message' => 'Failed',
                'status' => false
            ]);
        }
    }
}
