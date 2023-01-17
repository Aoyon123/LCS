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
            if (!$request->password) {
                $message = "Password Field Required!";
                return $this->responseError(403, false, $message);
            }
            if ($request->email_or_phone) {
                if (preg_match("/(^(\+88|0088)?(01){1}[3456789]{1}(\d){8})$/", $request->email_or_phone)) {
                    $credentials = ['phone' => $request->email_or_phone, 'password' => $request->password];
                } elseif (preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $request->email_or_phone)) {
                    $credentials = ['email' => $request->email_or_phone, 'password' => $request->password];
                } else {
                    $message = "Your Phone Or Email Not Valid!";
                    return $this->responseError(403, false, $message);
                }

                $user = User::orWhere('phone', $request->email_or_phone)->orWhere('email', $request->email_or_phone)->first();
                if ($user) {
                    if (Hash::check($request->password, $user->password)) {
                        if (!$token = auth()->attempt($credentials)) {
                            $message = "Invalid Credentials!";
                            return $this->responseError(403, false, $message);
                        }
                        return $this->createNewToken($token);
                    } else {
                        $message = "Your Password Not Match!";
                        return $this->responseError(403, false, $message);
                    }
                }
            } else {
                $message = "Please Enter Your Phone Or Email!";
                return $this->responseError(403, false, $message);
            }

            DB::commit();

        } catch (QueryException $e) {
            DB::rollBack();
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage(), []);
        }
        $message = "Invalid Credentials!";
        return $this->responseError(403, false, $message);
    }


    public function register(Request $request)
    {
        if ($request->type != null) {
            $type = strtolower($request->type);
            if ($type === 'citizen') {
                $citizenTotalData = User::select(DB::raw('count(id) as total'))
                    ->where('type', 'citizen')
                    ->first();
                $citizenData = $citizenTotalData->total + 1;
                $citizenCodeNo = 'cit-' . date('d-m-y-') . str_pad($citizenData, 4, '0', STR_PAD_LEFT);
                //return $citizenCodeNo;

                $request->validate([
                    'name' => 'required|string|max:50',
                    'phone' => 'required|max:11|min:11|regex:/(01)[0-9]{9}/|unique:users',
                    'password' => 'required|min:8',
                    'type' => 'required',
                    'rates' => 'nullable',


                ]);
            } elseif ($type === 'consultant') {

                $consultantTotalData = User::select(DB::raw('count(id) as total'))
                    ->where('type', 'consultant')
                    ->first();
                $consultantData = $consultantTotalData->total + 1;
                $consultantCodeNo = 'con-' . date('d-m-y-') . str_pad($consultantData, 4, '0', STR_PAD_LEFT);
                //  return $consultantCodeNo;

                $request->validate([
                    'name' => 'required|string|max:50',
                    'email' => 'email|unique:users,email',
                    'password' => 'required|min:8',
                    'type' => 'required',
                    'rates' => 'nullable',

                ]);
            }
        } else {

            $message = "Type cannot be null";
            return $this->responseError(404, false, $message);

        }

        DB::beginTransaction();
        try {

            $user = User::create([
                'name' => $request->name,
                'phone' => $request->phone ?? null,
                'email' => $request->email ?? null,
                'type' => strtolower($request->type),
                'rates' => $request->rates,
                'code' => $citizenCodeNo ?? $consultantCodeNo,
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
            if ($users) {
                $users->delete();
                $message = "Deleted Succesfully";
                DB::commit();
                return $this->responseSuccess(200, true, $message, []);
            }
        } catch (QueryException $e) {
            DB::rollBack();
        }
    }
    public function logout()
    {
        auth()->logout();
        //return response()->json(['message' => 'User successfully signed out']);
        $message = "User successfully logout";
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
            'expires_in' => auth()->factory()->getTTL() * 120,
            'user' => [
                'data' => auth()->user(),
                'role' => User::where('id', auth()->user()->id)->first()->getRoleNames(),
                'permissions' => User::where('id', auth()->user()->id)->first()->getAllPermissions()->pluck('name')
            ],
            //  return $this->responseSuccess(200, true, $message, $data);
        ]);
    }

}
