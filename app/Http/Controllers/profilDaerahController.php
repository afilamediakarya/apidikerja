<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\profil_daerah;
use Validator;
class profilDaerahController extends Controller
{
    public function list(){
        $data = profil_daerah::latest()->get();

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
            'nama_daerah' => 'required|string',
            'pimpinan_daerah' => 'required|string',
            'alamat' => 'required',
            'email' => 'required|unique:tb_profil_daerah',
            'no_telp' => 'required|unique:tb_profil_daerah',
            'logo' => 'required|image|mimes:jpeg,png,jpg,svg|max:2048'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = time().'.'.$file->getClientOriginalExtension();
            $file->storeAs('public/image',$filename);
        }

        $data = new profil_daerah();
        $data->nama_daerah = $request->nama_daerah;
        $data->pimpinan_daerah = $request->pimpinan_daerah;
        $data->alamat = $request->alamat;
        $data->email = $request->email;
        $data->no_telp = $request->no_telp;
        $data->logo = $filename;
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
        $data = profil_daerah::where('id',$params)->first();

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
            'nama_daerah' => 'required|string',
            'pimpinan_daerah' => 'required|string',
            'alamat' => 'required',
            'email' => 'required',
            'no_telp' => 'required',
            'logo' => 'image|mimes:jpeg,png,jpg,svg|max:2048'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

 

        $data =  profil_daerah::where('id',$params)->first();
        $data->nama_daerah = $request->nama_daerah;
        $data->pimpinan_daerah = $request->pimpinan_daerah;
        $data->alamat = $request->alamat;
        $data->email = $request->email;
        $data->no_telp = $request->no_telp;
        if (isset($request->logo)) {
            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $filename = time().'.'.$file->getClientOriginalExtension();
                $file->storeAs('public/image',$filename);
            }
            $data->logo = $filename;
        }
        
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
        $data = profil_daerah::where('id',$params)->first();
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
