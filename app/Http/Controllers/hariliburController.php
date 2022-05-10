<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use DB;
class hariliburController extends Controller
{
    public function list(){
   		$data = DB::table('tb_libur')->latest()->get();

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
    	$validator = Validator::make($request->all(),[
            'nama_libur' => 'required|string',
            'start_end' => 'required|date',
            'end_date' => 'required|date',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(),422);       
        }

        $data = DB::table('tb_libur')->insert([
		    'nama_libur' => $request->nama_libur,
		    'start_end' => $request->start_end,
		    'end_date' => $request->end_date,
		]);


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
        $data = DB::table('tb_libur')->where('id',$params)->first();
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

    public function update($id, Request $request){

		$validator = Validator::make($request->all(),[
            'nama_libur' => 'required|string',
            'start_end' => 'required|date',
            'end_date' => 'required|date',
        ]);


    	$data = DB::table('tb_libur')
              ->where('id', $id)
              ->update([
               	'nama_libur' => $request->nama_libur,
			    'start_end' => $request->start_end,
			    'end_date' => $request->end_date,
              ]);

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

    	$data = DB::table('tb_libur')->where('id', $params)->delete();

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
