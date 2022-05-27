<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\skp;
use App\Models\aspek_skp;
use App\Models\target_skp;
use App\Models\atasan;
use App\Models\review_realisasi_skp;
use App\Models\review_skp;
use App\Models\realisasi_skp;
use App\Models\satuan;
use App\Models\jabatan;
use App\Models\kegiatan;
use App\Models\pegawai;
use DB;
use Validator;
use Auth;
class skpController extends Controller
{
    public function list($params){
        if ($params == 'kepala') {
            return $this->list_skp_kepala();
        }else{
            return $this->list_skp_pegawai();
        }
    }

    public function list_skp_kepala(){
          $skp = skp::with('aspek_skp')->where('id_pegawai',Auth::user()->id_pegawai)->get();     
        if ($skp) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $skp
            ]);
        }else{
            return response()->json([
                'message' => 'empty data',
                'status' => false,
                 'data' => $skp
            ]);
        }
    }

    public function list_skp_pegawai(){
         $result = [];
        $groupSkpAtasan = [];
        $skpChild = '';
        $jabatanByPegawai = DB::table('tb_jabatan')->where('id_pegawai',Auth::user()->id_pegawai)->first();
        $get_skp_atasan = DB::table('tb_skp')->select('id_skp_atasan')->where('id_pegawai',Auth::user()->id_pegawai)->groupBy('tb_skp.id_skp_atasan')->get();

        foreach ($get_skp_atasan as $key => $value) {
            $getRencanaKerjaAtasan = '';
           if (!is_null($jabatanByPegawai->parent_id)) {
               $getSkpAtasan = DB::table('tb_skp')->select('id','rencana_kerja','jenis')->where('id',$value->id_skp_atasan)->first();
                $getRencanaKerjaAtasan = [
                'id' => $getSkpAtasan->id,
                'rencana_kerja' =>$getSkpAtasan->rencana_kerja
             ];
           }else{
             $getKegiatan= DB::table('tb_kegiatan')->select('id','nama_kegiatan','kode_kegiatan')->where('id',$value->id_skp_atasan)->first();

             if (isset($getKegiatan)) {
                $getRencanaKerjaAtasan = [
                    'id' => $getKegiatan->id,
                    'rencana_kerja' =>$getKegiatan->nama_kegiatan
                 ];
             }else{
                 $getRencanaKerjaAtasan = [];
             }

             
           }
           
            if ($getRencanaKerjaAtasan != []) {
                $skpChild = skp::with('aspek_skp')->where('id_skp_atasan',$getRencanaKerjaAtasan['id'])->where('id_pegawai',Auth::user()->id_pegawai)->get();
            }else{
                $skpChild = [];
            }
            $result[$key]['atasan'] = $getRencanaKerjaAtasan;
            $result[$key]['skp_child'] = $skpChild;
      
        }      


        if ($result) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $result
            ]);
        }else{
            return response()->json([
                'message' => 'empty data',
                'status' => false,
                 'data' => $result
            ]);
        }
    }

    public function store(Request $request){
     
        if($request->type_skp == 'kepala'){
            return $this->skp_kepala($request);
        }else{
            return $this->skp_pegawai($request);
        }
        
    }

    public function skp_kepala($request){
        $validator = Validator::make($request->all(),[
            'id_satuan_kerja' => 'required|numeric',
            'jenis_kinerja' => 'required',
            'rencana_kerja' => 'required',
            'tahun' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(),422);       
        }
        
        $skp = new skp();
        $skp->id_pegawai = Auth::user()->id_pegawai;
        $skp->id_satuan_kerja = $request->id_satuan_kerja;
        $skp->jenis = $request->jenis_kinerja;
        $skp->rencana_kerja = $request->rencana_kerja;
        $skp->tahun = $request->tahun;
        $skp->save();

        for ($i=0; $i < 13; $i++) { 
            $review_realisasi_skp = new review_realisasi_skp();
            $review_realisasi_skp->id_skp = $skp->id;
            $review_realisasi_skp->kesesuaian = 'tidak';
            $review_realisasi_skp->bulan = $i+1;
            $review_realisasi_skp->save();
        }

        for ($i=0; $i < count($request->indikator_kerja_individu); $i++) { 
                $aspek = new aspek_skp();
                $aspek->id_skp = $skp->id;
                $aspek->aspek_skp = "iki";
                $aspek->iki = $request->indikator_kerja_individu[$i];
                $aspek->satuan = $request->satuan[$i];
                $aspek->save();

                for ($x=0; $x < 12; $x++) { 
                    $realisasi_skp = new realisasi_skp();
                    $realisasi_skp->id_aspek_skp = $aspek->id;
                    $realisasi_skp->realisasi_bulanan = 0;
                    $realisasi_skp->bulan = $x+1;
                    $realisasi_skp->save();
                }

                for ($x=0; $x < count($request->target_[$i]); $x++) { 
                    $target = new target_skp();
                    $target->id_aspek_skp = $aspek->id;
                    $target->target = $request->target_[$i][$x];
                    $target->bulan = $x+1;
                    $target->save();
                }
                
        }

        if ($skp) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $skp
            ]);
        }else{
            return response()->json([
                'message' => 'Failed',
                'status' => false
            ],422);
        }

    }

    public function skp_pegawai($request){
        $validator = Validator::make($request->all(),[
            'id_satuan_kerja' => 'required|numeric',
            'id_skp_atasan' => 'required|numeric',
            'jenis' => 'required',
            'rencana_kerja' => 'required',
            'tahun' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(),422);       
        }
        
        $skp = new skp();
        $skp->id_pegawai = Auth::user()->id_pegawai;
        $skp->id_satuan_kerja = $request->id_satuan_kerja;
        $skp->id_skp_atasan = $request->id_skp_atasan;
        $skp->jenis = $request->jenis;
        $skp->rencana_kerja = $request->rencana_kerja;
        $skp->tahun = $request->tahun;
        $skp->save();

        $review = new review_skp();
        $review->id_skp = $skp->id;
        $review->kesesuaian = 'tidak';
        $review->save();

        for ($i=0; $i < 13; $i++) { 
            $review_realisasi_skp = new review_realisasi_skp();
            $review_realisasi_skp->id_skp = $skp->id;
            $review_realisasi_skp->kesesuaian = 'tidak';
            $review_realisasi_skp->bulan = $i+1;
            $review_realisasi_skp->save();
        }

        foreach ($request['aspek'] as $key => $value) {
            $aspek = new aspek_skp();
            $aspek->id_skp = $skp->id;
            $aspek->aspek_skp = $value['type_aspek'];
            $aspek->iki = $value['iki'];
            $aspek->satuan = $value['satuan'];
            $aspek->save();

            for ($x=0; $x < 12; $x++) { 
                $realisasi_skp = new realisasi_skp();
                $realisasi_skp->id_aspek_skp = $aspek->id;
                $realisasi_skp->realisasi_bulanan = 0;
                $realisasi_skp->bulan = $x+1;
                $realisasi_skp->save();
            }

            foreach ($value['target'] as $index => $res) {
                $target = new target_skp();
                $target->id_aspek_skp = $aspek->id;
                $target->target = $res;
                $target->bulan = $index+1;
                $target->save();
            }
        }


        if ($skp) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $skp
            ]);
        }else{
            return response()->json([
                'message' => 'Failed',
                'status' => false
            ],422);
        }
    }

    public function show($params){
        $data = skp::with('aspek_skp')->where('id',$params)->first();
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

        if($request->type_skp == 'kepala'){
            return $this->update_skp_kepala($params,$request);
        }else{
            return $this->update_skp_pegawai($params,$request);
        }
      
    }

    public function update_skp_kepala($params,$request){

        $validator = Validator::make($request->all(),[
            'id_satuan_kerja' => 'required|numeric',
            'jenis_kinerja' => 'required',
            'rencana_kerja' => 'required',
            'tahun' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(),422);       
        }

        $clearSkp = $this->delete($params);
        
        $skp = new skp();
        $skp->id_pegawai = Auth::user()->id_pegawai;
        $skp->id_satuan_kerja = $request->id_satuan_kerja;
        $skp->jenis = $request->jenis_kinerja;
        $skp->rencana_kerja = $request->rencana_kerja;
        $skp->tahun = $request->tahun;
        $skp->save();

        for ($i=0; $i < 13; $i++) { 
            $review_realisasi_skp = new review_realisasi_skp();
            $review_realisasi_skp->id_skp = $skp->id;
            $review_realisasi_skp->kesesuaian = 'tidak';
            $review_realisasi_skp->bulan = $i+1;
            $review_realisasi_skp->save();
        }

        for ($i=0; $i < count($request->indikator_kerja_individu); $i++) { 
                $aspek = new aspek_skp();
                $aspek->id_skp = $skp->id;
                $aspek->aspek_skp = "iki";
                $aspek->iki = $request->indikator_kerja_individu[$i];
                $aspek->satuan = $request->satuan[$i];
                $aspek->save();

                for ($x=0; $x < 12; $x++) { 
                    $realisasi_skp = new realisasi_skp();
                    $realisasi_skp->id_aspek_skp = $aspek->id;
                    $realisasi_skp->realisasi_bulanan = 0;
                    $realisasi_skp->bulan = $x+1;
                    $realisasi_skp->save();
                }

                for ($x=0; $x < count($request->target_[$i]); $x++) { 
                    $target = new target_skp();
                    $target->id_aspek_skp = $aspek->id;
                    $target->target = $request->target_[$i][$x];
                    $target->bulan = $x+1;
                    $target->save();
                }
                
        }

        if ($skp) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $skp
            ]);
        }else{
            return response()->json([
                'message' => 'Failed',
                'status' => false
            ],422);
        }
    }


    public function update_skp_pegawai($params,$request){
        $validator = Validator::make($request->all(),[
            'id_satuan_kerja' => 'required|numeric',
            'id_skp_atasan' => 'required|numeric',
            'jenis' => 'required',
            'rencana_kerja' => 'required',
            'tahun' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }


        $clearSkp = $this->delete($params);

        if ($clearSkp) {
            $skp = new skp();
            $skp->id_pegawai = Auth::user()->id_pegawai;
            $skp->id_satuan_kerja = $request->id_satuan_kerja;
            $skp->id_skp_atasan = $request->id_skp_atasan;
            $skp->jenis = $request->jenis;
            $skp->rencana_kerja = $request->rencana_kerja;
            $skp->tahun = $request->tahun;
            $skp->save();

            foreach ($request['aspek'] as $key => $value) {
                $aspek = new aspek_skp();
                $aspek->id_skp = $skp->id;
                $aspek->aspek_skp = $value['type_aspek'];
                $aspek->iki = $value['iki'];
                $aspek->satuan = $value['satuan'];
                $aspek->save();
                foreach ($value['target'] as $index => $res) {
                    $target = new target_skp();
                    $target->id_aspek_skp = $aspek->id;
                    $target->target = $res;
                    $target->bulan = $index+1;
                    $target->save();
                }
            }

            if ($skp) {
                return response()->json([
                    'message' => 'Success',
                    'status' => true,
                    'data' => $skp
                ]);
            }else{
                return response()->json([
                    'message' => 'Failed',
                    'status' => false
                ],422);
            }
        }
    }

    public function delete($params){
        $data = skp::where('id',$params)->first();
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

    public function satuan(){
        $result = [];
        $data = satuan::where('status','active')->latest()->get();
        foreach ($data as $key => $value) {
            $result[$key] = [
                'value'=> $value->nama_satuan
            ];
        }

        return response()->json($result);
        // return collect($data)->pluck('nama_satuan')->toArray();
    }

    public function optionSkp(){
        $result = [];
        // $jabatanByPegawai = DB::table('tb_jabatan')->where('id_pegawai',Auth::user()->id_pegawai)->first();
        $jabatanByPegawai = DB::table('tb_jabatan')->select('tb_jabatan.parent_id','tb_jenis_jabatan.level')->join('tb_jenis_jabatan','tb_jabatan.id_jenis_jabatan','=','tb_jenis_jabatan.id')->where('id_pegawai',Auth::user()->id_pegawai)->first();
        $pegawai = pegawai::where('id',Auth::user()->id_pegawai)->first();

        if (isset($jabatanByPegawai)) {
            if (!is_null($jabatanByPegawai->parent_id)) {
                if ($jabatanByPegawai->level != "1") {
                    $atasan = DB::table('tb_jabatan')->where('id',$jabatanByPegawai->parent_id)->first();
                    // return $atasan;
                    
                    if (isset($atasan)) {
                        $getSkp = skp::where('id_pegawai',$atasan->id_pegawai)->get();
                        foreach ($getSkp as $key => $value) {
                            $result[$key] = [
                                'id' => $value->id,
                                'value'=> $value->rencana_kerja
                            ];
                        }
                    }

                   
                
                     return response()->json([
                        'message' => 'Success',
                        'status' => true,
                        'data' => $result
                    ]);

                }   

            }else{

                if ($jabatanByPegawai->level == "1") {
                     $kegiatan = kegiatan::where('id_satuan_kerja',$pegawai['id_satuan_kerja'])->latest()->get();
                    foreach ($kegiatan as $key => $value) {
                        $result[$key] = [
                            'id' => $value->id,
                            'value'=> $value->nama_kegiatan
                        ];
                    }
                    // return $result;
                    return response()->json([
                     'message' => 'Success',
                           'status' => true,
                            'data' => $result
                    ]);  
                }else{
                    return response()->json([
                        'message' => 'Data tidak ada',
                        'status' => false,
                        'data' => $result
                    ]); 
                }
              
            }
          
        }else{
            return response()->json([
                'message' => 'Data tidak ada',
                'status' => false,
                'data' => $result
            ]); 
        }
        
    }

}
