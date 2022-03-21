<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\perilaku;
use App\Models\situasi;
use App\Models\indikator;
use Validator;
class perilakuController extends Controller
{
    public function list(){
        $data = perilaku::latest()->get();

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


    public function store(Request $request){
        // return $request->all();
        $validator = Validator::make($request->all(),[
            'perilaku' => 'required',
            'definisi_perilaku' => 'required|string',
            'kesimpulan_perilaku' => 'required|string',
            'number' => 'required|numeric',
            'untuk'=> 'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        $data = new perilaku();
        $data->perilaku	 = $request->perilaku;
        $data->definisi_perilaku = $request->definisi_perilaku;
        $data->kesimpulan_perilaku = $request->kesimpulan_perilaku;
        $data->number = $request->number;
        $data->untuk = json_encode($request->untuk);
        $data->save();

        foreach ($request->data as $key => $value) {
            $situasi = new situasi();
            $situasi->id_perilaku = $data->id;
            $situasi->situasi = $value['situasi'];
            $situasi->urutan = $value['urutan'];  
            $situasi->save();

            foreach($value['indikator'] as $in => $res){
                $indikator = new indikator();
                $indikator->id_situasi = $situasi->id;
                $indikator->urutan = $res['urutan'];
                $indikator->indikator = $res['indikator'];
                $indikator->untuk = json_encode($res['untuk']);
                $indikator->save();
            }
        }

        // for ($i=0; $i < ; $i++) { 
        //     count($request->situasi);
        // }


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

    public function show($params){
        $data = perilaku::where('id',$params)->first();

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

    public function update($params,Request $request){
        // return $request->all();
        $validator = Validator::make($request->all(),[
            'perilaku' => 'required',
            'definisi_perilaku' => 'required|string',
            'kesimpulan_perilaku' => 'required|string',
            'number' => 'required|numeric',
            'untuk'=> 'required|string'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        $getSituasi=situasi::where('id_perilaku',$params)->delete();
        // $getSituasi->delete();

        $data = perilaku::where('id',$params)->first();
        $data->perilaku	 = $request->perilaku;
        $data->definisi_perilaku = $request->definisi_perilaku;
        $data->kesimpulan_perilaku = $request->kesimpulan_perilaku;
        $data->number = $request->number;
        $data->untuk = json_encode($request->untuk);
        $data->save();


        foreach ($request->data as $key => $value) {
            $situasi = new situasi();
            $situasi->id_perilaku = $data->id;
            $situasi->situasi = $value['situasi'];
            $situasi->urutan = $value['urutan'];  
            $situasi->save();

            foreach($value['indikator'] as $in => $res){
                $indikator = new indikator();
                $indikator->id_situasi = $situasi->id;
                $indikator->urutan = $res['urutan'];
                $indikator->indikator = $res['indikator'];
                $indikator->untuk = json_encode($res['untuk']);
                $indikator->save();
            }
        }


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

    public function delete($params){
        $data = perilaku::where('id',$params)->first();
        $data->delete();

        if ($data) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
            ]);
        }else{
            return response()->json([
                'message' => 'Failed',
                'status' => false
            ]);
        }
    }
}
