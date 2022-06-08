<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\jabatan;
use App\Models\pegawai;
use App\Models\jenis_jabatan;
use Auth;
use DB;
use Illuminate\Validation\Rule;
class jabatanController extends Controller
{
    public function list(){
        $data = '';
        if (Auth::user()->role == 'super_admin') {
           $data = jabatan::latest()->get();
        }else{
            $pegawai = pegawai::select('id_satuan_kerja')->where('id',Auth::user()->id_pegawai)->first();
            $data = jabatan::where('id_satuan_kerja',$pegawai->id_satuan_kerja)->latest()->get();
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

    public function store(Request $request){
       
        $validator = Validator::make($request->all(),[
            'id_satuan_kerja' => 'required|numeric',
            'id_jenis_jabatan' => 'required|numeric',
            'parent_id' => Rule::requiredIf($request->level > 1),
            'pembayaran_tpp' => 'required',
            'nama_jabatan' => 'required',
            'status_jabatan' => 'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $filename = time().'.'.$file->getClientOriginalExtension();
            $file->storeAs('public/image',$filename);
        }

        $data = new jabatan();
        $data->id_satuan_kerja = $request->id_satuan_kerja;
        $data->id_jenis_jabatan = $request->id_jenis_jabatan;
        $data->id_pegawai = $request->id_pegawai;
        $data->parent_id = $request->parent_id;
        $data->nama_jabatan = $request->nama_jabatan;
        $data->status_jabatan = $request->status_jabatan;
        $data->pembayaran_tpp = $request->pembayaran_tpp;
        $data->nilai_jabatan = str_replace(',','',$request->nilai_jabatan);
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
        $result = [];
        $parent_ = '';
        $data = jabatan::where('id',$params)->first();
        $parent = jabatan::where('id',$data->parent_id)->first();

        if (isset($parent)) {
            $parent_ = $parent->id;
        }else{
            $parent_ = $parent;
        }

        $result = [
            'id' => $data->id,
            'id_satuan_kerja' => $data->id_satuan_kerja,
            'nama_jabatan' => $data->nama_jabatan,
            'id_jenis_jabatan' => $data->id_jenis_jabatan,
            'nilai_jabatan' =>  $data->nilai_jabatan,
            'status_jabatan' => $data->status_jabatan,
            'pegawai' => $data->pegawai,
            'pembayaran_tpp' => $data->pembayaran_tpp,
            'parent_id' => [
                'parent_id' => $parent_,
                'jenis_jabatan' => $data->id_jenis_jabatan,
            ],
            
        ];


        if ($result) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $result
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
            'id_satuan_kerja' => 'required|numeric',
            'id_jenis_jabatan' => 'required|numeric',
            'parent_id' => Rule::requiredIf($request->level > 1),
            'pembayaran_tpp' => 'required',
            'nama_jabatan' => 'required',
            'status_jabatan' => 'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        $data = jabatan::where('id',$params)->first();
        $data->id_satuan_kerja = $request->id_satuan_kerja;
        $data->id_jenis_jabatan = $request->id_jenis_jabatan;
        $data->id_pegawai = $request->id_pegawai;
         $data->parent_id = $request->parent_id;
        $data->nama_jabatan = $request->nama_jabatan;
        $data->status_jabatan = $request->status_jabatan;
        $data->pembayaran_tpp = $request->pembayaran_tpp;
        $data->nilai_jabatan = str_replace(',','',$request->nilai_jabatan);
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
        $data = jabatan::where('id',$params)->first();
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

    public function jabatanAtasan($level,$id_satuan_kerja){
        $current_user = pegawai::where('id',Auth::user()->id_pegawai)->first();
        $result = [];
        // return $current_user['id_satuan_kerja'];
        $data = jabatan::where('id_satuan_kerja',$id_satuan_kerja)->where('level',$level-1)->get();

        foreach ($data as $key => $value) {
            $result[$key] = [
                'id' => $value->id,
                'value'=> $value->nama_jabatan
            ];
        }

        return response()->json($result);
    }

    public function getPegawaiBySatuanKerja(){
        $current_user = pegawai::where('id',Auth::user()->id_pegawai)->first();
        // return $current_user;

        $data = pegawai::where('id_satuan_kerja',$current_user['id_satuan_kerja'])->get();

        foreach ($data as $key => $value) {
            $result[] = [
                'id' => $value->id,
                'value'=> $value->nama
            ];            
        }

        return response()->json($result);

    }

    public function getOptionJenisJabatan(){
        $data = jenis_jabatan::orderBy('id', 'DESC')->get();

        foreach ($data as $key => $value) {
            $result[$key] = [
                'id' => $value->id,
                'value'=> $value->jenis_jabatan
            ];
        }

        return response()->json($result);
    }

    public function getParent($params){
        $result = [];
        $current_jenis_jabatan = jenis_jabatan::where('id',$params)->first();
        $current_user = pegawai::where('id',Auth::user()->id_pegawai)->first();
        // return $current_jenis_jabatan['level'];
        $getParent = DB::table('tb_jabatan')->select('tb_jabatan.nama_jabatan','tb_pegawai.nama','tb_jabatan.id_pegawai','tb_jabatan.id')->join('tb_pegawai','tb_pegawai.id','=','tb_jabatan.id_pegawai')->join('tb_jenis_jabatan','tb_jenis_jabatan.id','=','tb_jabatan.id_jenis_jabatan')->where('tb_jenis_jabatan.level','<',$current_jenis_jabatan['level'])->where('tb_jabatan.id_satuan_kerja',$current_user['id_satuan_kerja'])->get();
     
        // return $getParent;
        
        foreach ($getParent as $key => $value) {
            if ($value->id_pegawai != $current_user->id) {
                $result[] = [
                    'id' => $value->id,
                    'value'=> $value->nama.' - '.$value->nama_jabatan
                ];    
            }   
        }

        return response()->json($result);

    }
}
