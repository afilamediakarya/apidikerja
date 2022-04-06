<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Auth;
use Validator;
use App\Models\User;
use App\Models\pegawai;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request){
        if (!Auth::attempt($request->only('username', 'password')))
        {
            return response()
                ->json(['message' => 'Unauthorized'], 401);
        }

        $user = User::where('username', $request['username'])->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Hi '.$user->username.', Berhasil Login',
            'access_token' => $token, 
            'role' => $user->role,
            'current' => $user,
            'token_type' => 'Bearer', 
        ]);
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

        $validator = Validator::make($request->all(), [
            'password_baru' => 'required|string|min:8',
            'password_lama' => 'required'
        ]);
    
        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        $user = User::where('id',Auth::user()->id)->first();
        // return $user;
        if (isset($user)) {    
            $password = Hash::check($request->password_lama, $user->password);
            
            if ($password == true) {
                    $user->password = Hash::make($request->password_baru);
                    $user->save();
            }else{
                throw ValidationException::withMessages([
                    'message' => ['Password lama salah.'],
                ]);
            }
        }

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
          $user = User::findOrFail(Auth::user()->id);

        if ($user) {
            return response()->json([
                'message' => 'Success',
                'status' => true,
                'data' => $user
            ]);
        }else{
            throw ValidationException::withMessages([
                'message' => 'Failed',
                'status' => false
            ]);
        }
    }

    public function listUsersByOpd(){
        $data = pegawai::where('role','admin_opd')->get();
        
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
        $data = users::where('id_pegawai',$params)->first();
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
        $data = users::where('id_pegawai',$params)->first();
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
}
