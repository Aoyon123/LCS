<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;


class AuthController extends Controller
{
    use ResponseTrait;
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function index()
    {
        $data = User::all();

        $message = "Succesfully Data Shown";
        return $this->responseSuccess(200, true, $message, $data);
    }


    public function login(Request $request)
    {
        // return $request->all();
        // if($request->type === 'citizen'){
        //     $validator = Validator::make($request->all(), [
        //         'phone' => 'required|min:11',
        //         'password' => 'required|string|min:6',
        //     ]);
        // }elseif($request->type === 'consultant'){
        //     $validator = Validator::make($request->all(), [
        //         'email' => 'required|email',
        //         'password' => 'required|string|min:6',
        //     ]);
        // }
        DB::beginTransaction();

        try {

            if (preg_match("/(^(\+88|0088)?(01){1}[3456789]{1}(\d){8})$/", $request->email_or_phone)) {
                $credentials = ['phone' => $request->email_or_phone, 'password' => $request->password];
            } else {
                $credentials = ['email' => $request->email_or_phone, 'password' => $request->password];
            }

            DB::commit();

        } catch (QueryException $e) {
            DB::rollBack();
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage(), []);
        }

        if (!$token = auth()->attempt($credentials)) {
            $message = "Invalid Credentials";
            return $this->responseError(403, false, $message);
        }

        return $this->createNewToken($token);
    }


    public function register(Request $request)
    {
        if ($request->type === 'citizen') {
            $request->validate([
                'name' => 'required|string|max:50',
                'phone' => 'max:11|min:11|regex:/(01)[0-9]{9}/|unique:users',
                'password' => 'required|min:8',
                'type' => 'required',
            ]);
        } elseif ($request->type === 'consultant') {
            $request->validate([
                'name' => 'required|string|max:50',
                'email' => 'email|unique:users,email',
                'password' => 'required|min:8',
                'type' => 'required',
            ]);
        }

        DB::beginTransaction();
        try {

            $user = User::create([
                'name' => $request->name,
                'phone' => $request->phone ?? null,
                'email' => $request->email ?? null,
                'type' => $request->type,
                'password' => Hash::make($request->password)
            ]);

            $role = Role::where('name', $request->type)->first();

            $user->assignRole($role);

            DB::commit();
            $message = $request->type . " Registration Successfull";
            return $this->responseSuccess(200, true, $message, $user);

        } catch (QueryException $e) {
            DB::rollBack();
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, $e->getMessage());
        }
    }
    public function retrieve($id)
    {
        DB::beginTransaction();
        try {
            $data = User::findOrFail($id);
            $message = "Data Found";
            DB::commit();
            return $this->responseSuccess(200, true, $message, $data);
        } catch (QueryException $e) {
            DB::rollBack();
        }
    }

    public function destroy(Request $request)
    {
        DB::beginTransaction();
        try {
            $users = DB::table('users')->whereIn('id', $request->all());
            $users->delete();
            $message = "Deleted Succesfully";
            DB::commit();
            return $this->responseSuccess(200, true, $message, []);

        } catch (QueryException $e) {
            DB::rollBack();
        }
    }
    public function logout()
    {
        auth()->logout();
        //return response()->json(['message' => 'User successfully signed out']);
        $message = "User successfully signed out";
        return $this->responseSuccess(200, true, $message, []);
    }

    public function refresh()
    {
        return $this->createNewToken(auth()->refresh());
    }

    public function userProfile()
    {
        return response()->json(auth()->user());
    }

    protected function createNewToken($token)
    {

        return response()->json([
            'status_code' => 200,
            'message' => 'Login Succesfull',
            'status' => true,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => [
                'data' => auth()->user(),
                'role' => User::where('id', auth()->user()->id)->first()->getRoleNames(),
                'permissions' => User::where('id', auth()->user()->id)->first()->getAllPermissions()->pluck('name')
            ],
            //  return $this->responseSuccess(200, true, $message, $data);
        ]);
    }

}
