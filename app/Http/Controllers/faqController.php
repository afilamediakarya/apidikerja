<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\faq;
use Validator;
class faqController extends Controller
{
    public function list(){
        $data = faq::latest()->get();

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
            'pertanyaan' => 'required|string',
            'jawaban' => 'required|string',
            'urutan' => 'required|numeric',
            'status' => 'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        $data = new faq();
        $data->pertanyaan = $request->pertanyaan;
        $data->jawaban = $request->jawaban;
        $data->urutan = $request->urutan;
        $data->status = $request->status;
        $data->save();


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
        $data = faq::where('id',$params)->first();

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
        $validator = Validator::make($request->all(),[
            'pertanyaan' => 'required|string',
            'jawaban' => 'required|string',
            'urutan' => 'required|numeric',
            'status' => 'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        $data =  faq::where('id',$params)->first();
        $data->pertanyaan = $request->pertanyaan;
        $data->jawaban = $request->jawaban;
        $data->urutan = $request->urutan;
        $data->status = $request->status;
        $data->save();

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
        $data = faq::where('id',$params)->first();
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
