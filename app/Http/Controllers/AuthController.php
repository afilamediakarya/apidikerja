<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Auth;
use Validator;
use App\Models\User;
use App\Models\pegawai;
use App\Models\jabatan;
use Illuminate\Validation\ValidationException;
use DB;
use Illuminate\Support\Facades\Redis;
class AuthController extends Controller
{
    public function login(Request $request){
        
        if (!Auth::attempt($request->only('username', 'password')))
        {
            return response()
                ->json(['message' => 'Unauthorized','messages_' => 'Data Pengguna tidak di temukan'], 401);
        }

        $level = 0;
        $level_ = [];
        $status_login_fails = '';
        $token = '';
        $user = array();


        if ($request->username !== 'super_admin' && $request->username !== 'adminkeuangan') {
            $user = User::select('users.id','id_pegawai','role','tb_pegawai.id_satuan_kerja','nama','nip','golongan','tb_satuan_kerja.nama_satuan_kerja')->join('tb_pegawai','tb_pegawai.id','users.id_pegawai')->join('tb_satuan_kerja','tb_satuan_kerja.id','tb_pegawai.id_satuan_kerja')->where('username', $request['username'])->firstOrFail();
        }else{
            $user = User::where('username', $request['username'])->firstOrFail();
        }

         if ($user['role'] == 'admin_opd' || $user['role'] == 'super_admin' || $user['role'] == 'keuangan') {
               $token = $user->createToken('auth_token')->plainTextToken; 
                 return response()->json([
                'message' => 'Hi '.$user->username.', Berhasil Login',
                'access_token' => $token, 
                'role' => $user->role,
                'current' => $user,
                'level_jabatan' => $level,
                'token_type' => 'Bearer', 
            ]);
        }

        $jabatan = jabatan::with('jenis_jabatan')->select('id','id_jenis_jabatan')->where('id_pegawai',Auth::user()->id_pegawai)->get();
        
        if (count($jabatan) > 0) {
            foreach ($jabatan as $key => $value) {
                if (!is_null($value['jenis_jabatan'])) {
                    
                    $level_[] = $value['jenis_jabatan']['level'];   
                    $token = $user->createToken('auth_token')->plainTextToken; 
                }else{

                    $status_login_fails = 'Jabatan tidak di temukan, Mohon hubungi admin opd';
                }   
            }
        }else{
             $status_login_fails = 'Jabatan tidak di temukan, Mohon hubungi admin opd';
        }

        if (count($level_) > 0) {
           $level = max($level_);
        }else{
            $level = 0;
        }

        if ($token !== '') {
            return response()->json([
                'message' => 'Hi '.$user->username.', Berhasil Login',
                'access_token' => $token, 
                'role' => $user->role,
                'current' => $user,
                // 'check_atasan'=> $data,
                'level_jabatan' => $level,
                'token_type' => 'Bearer', 
            ]);
        }else{
              return response()->json([
                'message' => 'Gagal Login',
                 'messages_' => $status_login_fails
            ],422);
        }
    }

    public function register_user(Request $request){
        $validator = Validator::make($request->all(),[
            'username' => 'required|string|min:8|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        $data = New User();
        if ($request->role == 'pegawai' || $request->role == 'admin_opd') {
            $pegawai = pegawai::where('id',$request->id_pegawai)->first();
            $data->id_pegawai = $request->id_pegawai;
            $data->username = $pegawai['nip'];

        }else{
            $data->username = $request->username;
        }
        
        $data->email  = $request->email;
        $data->password = Hash::make($request->password);
        $data->role = $request->role;
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

    public function change_password(Request $request){
        // return $request->all();
        $validator = Validator::make($request->all(), [
            'password_baru' => 'required|string|min:8',
            'password_lama' => 'required'
        ]);
    
        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        $user = User::where('id',Auth::user()->id)->first();
        // return $user->password;
        if (isset($user)) {    
            $password = Hash::check($request->password_lama, $user->password);
            // dd($password);    
            if ($password == true) {
                    $user->password = Hash::make($request->password_baru);
                    $user->save();
            }else{
                return response()->json([
                    'message' => 'Password lama salah',
                    'status' => false,
                ],422);
            }
        }

        if ($user) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $user
            ]);
        }else{
            return response()->json([
                'message' => 'Failed',
                'status' => false,
            ],422);
        }

    }

    public function face_id(Request $request){
        $data = pegawai::where('id',Auth::user()->id_pegawai)->first();
        $data->face_character = $request->id_face;
        $data->save();

        if ($data) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $data
            ]);
        }else{
            throw ValidationException::withMessages([
                'message' => 'Failed',
                'status' => false
            ]);
        }
    }

    public function current_user(){
          // $data = User::findOrFail(Auth::user()->id);
        $current = array();

        $getDataCache= Redis::get('current_user'.Auth::user()->id_pegawai);
		$current = json_decode($getDataCache);

		if (!$getDataCache) {

            $user = DB::table('users')->select('users.id','users.id_pegawai','users.role','tb_pegawai.id_satuan_kerja','tb_pegawai.nama','tb_pegawai.nip','tb_pegawai.id','tb_pegawai.face_character','tb_pegawai.golongan','tb_pegawai.lulus_pendidikan','tb_pegawai.jurusan','tb_pegawai.agama','tb_pegawai.status_perkawinan','tb_pegawai.jenis_kelamin','tb_pegawai.tmt_golongan','tb_satuan_kerja.nama_satuan_kerja','tb_satuan_kerja.inisial_satuan_kerja','tb_satuan_kerja.kode_satuan_kerja','tb_satuan_kerja.status_kepala','tb_lokasi.nama_lokasi','tb_lokasi.lat','tb_lokasi.long')->join('tb_pegawai','users.id_pegawai','=','tb_pegawai.id')->where('users.id',Auth::user()->id)->join('tb_satuan_kerja','tb_pegawai.id_satuan_kerja','=','tb_satuan_kerja.id')->join('tb_jabatan','tb_pegawai.id','=','tb_jabatan.id_pegawai')->join('tb_lokasi','tb_jabatan.id_lokasi','=','tb_lokasi.id')->first();

            if (isset($user)) {
            $current = [
                    'id' => $user->id,
                    'id_pegawai' => $user->id_pegawai,
                    'role' => $user->role,
                    'pegawai' => [
                        'id' => $user->id_pegawai,
                        'id_satuan_kerja' => $user->id_satuan_kerja,
                        'nama' => $user->nama,
                        'nip' => $user->nip,
                        'golongan' => $user->golongan,
                        'face_character' => $user->face_character,
                        'lulus_pendidikan' => $user->lulus_pendidikan,
                        'jurusan' => $user->jurusan,
                        'agama' => $user->agama,
                        'status_perkawinan' => $user->status_perkawinan,
                        'jenis_kelamin' => $user->jenis_kelamin,
                        'tmt_golongan' => $user->tmt_golongan,
                        'satuan_kerja' => [
                            'id' => $user->id_satuan_kerja,
                            'nama_satuan_kerja' => $user->nama_satuan_kerja,
                            'inisial_satuan_kerja' => $user->inisial_satuan_kerja,
                            'kode_satuan_kerja' => $user->kode_satuan_kerja,
                            'nama_lokasi' => $user->nama_lokasi,
                            'lat_location' => $user->lat,
                            'long_location' => $user->long,
                            'lokasi_apel_lat' => '-5.5585896680010904',
                            'lokasi_apel_long' => '120.19320969890903',
                            'status_kepala' => $user->status_kepala
                        ],
                        
                    ],
                ];
            }else{
                return response()->json([
                    'message' => 'Gagal Login',
                    'messages_' => 'Jabatan tidak di temukan, Mohon hubungi admin opd'
                ],422);
            }

            Redis::set('current_user_'.Auth::user()->id_pegawai, json_encode($current));
            Redis::expire('current_user_'.Auth::user()->id_pegawai, 1800);
            
        }

        if ($current) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $current
            ]);
        }else{
            throw ValidationException::withMessages([
                'message' => 'Failed',
                'status' => false
            ]);
        }
    }

    public function listUsersByOpd(){
        // $data = User::where('role','admin_opd')->get();
        $data = pegawai::join('users','tb_pegawai.id', '=', 'users.id_pegawai')->where('users.role','admin_opd')->get();
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
            ],422);
        }
    }

    public function pegawailistBySatuanKerja($params){
        $result = [];
        $data = pegawai::where('id_satuan_kerja',$params)->get();
      
        foreach ($data as $key => $value) {
            $result[$key] = [
                'id' => $value->id,
                'value'=> $value->nama
            ];
        }

        return response()->json($result);
    }

    public function changeRoleAdmin($params){
        $data = User::where('id_pegawai',$params)->first();
        $data->role = 'admin_opd';
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
            ],422);
        }

    }

    public function changeRolePegawai($params){
        $data = User::where('id_pegawai',$params)->first();
        $data->role = 'pegawai';
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
            ],422);
        }
    }

    public function logout(Request $request)
    {
        auth()->user()->tokens()->delete();
        // $user->tokens()->delete();
        return [
            'message' => 'You have successfully logged out and the token was successfully deleted'
        ];
    }

    public function removeCache(){
        $cache_Remove = Redis::flushAll();
        return $cache_Remove;
    }
}
