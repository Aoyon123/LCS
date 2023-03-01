<?php

namespace App\Http\Controllers;

use App\Http\Helper\SMSHelper;
use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;


class AuthController extends Controller
{
    use ResponseTrait;
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register','registrationWithOTP','refreshOTP']]);
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
                        if (!$token = JWTAuth::attempt($credentials, ['exp' => Carbon::now()->addMinutes(2)->timestamp])) {
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

        $userExist = User::where('phone', $request->phone)->first();
        if ($userExist && $userExist->is_phone_verified) {
            $message = $userExist->phone . " already registered, now you can login";
            return $this->responseSuccess(200, true, $message, $userExist);
        }

        if ($userExist && $userExist->is_phone_verified == null) {

            $otp = SMSHelper::generateOTP();
            $smsMessage = $otp . ' is your LCS verification code';
            $messageSuccess = SMSHelper::sendSMS($userExist->phone, $smsMessage);

            if ($messageSuccess) {
                $message = "Phone Number Already Taken, Verification Code Send Successfully, Check Your Message!";
                return $this->responseSuccess(410, true, $message, $userExist);
            } else {
                return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, 'Something wrong');
            }
        }


        if ($request->type != null) {
            //converts all the uppercase english alphabets present in the string to lowercase
            $type = strtolower($request->type);

            if ($type === 'citizen') {
                $citizenTotalData = User::select(DB::raw('count(id) as total'))
                    ->where('type', 'citizen')
                    ->first();
                //  return $citizenTotalData;
                $citizenData = $citizenTotalData->total + 1;
                $citizenCodeNo = 'cit-' . date('dmy-') . str_pad($citizenData, 4, '0', STR_PAD_LEFT);

                $request->validate([
                    'first_name' => 'required|string|max:50',
                    'last_name' => 'required|string|max:50',
                    'phone' => 'required|max:11|min:11|regex:/(01)[0-9]{9}/|unique:users',
                    'password' => 'required|min:8',
                    'type' => 'required',
                    'terms_conditions' => 'required'
                ]);
            } elseif ($type === 'consultant') {

                $consultantTotalData = User::select(DB::raw('count(id) as total'))
                    ->where('type', 'consultant')
                    ->first();
                $consultantData = $consultantTotalData->total + 1;
                $consultantCodeNo = 'con-' . date('dmy-') . str_pad($consultantData, 4, '0', STR_PAD_LEFT);
                //  return $consultantCodeNo;
                $request->validate([
                    'first_name' => 'required|string|max:50',
                    'last_name' => 'required|string|max:50',
                    'phone' => 'required|max:11|min:11|regex:/(01)[0-9]{9}/|unique:users',
                    'email' => 'required|email|unique:users,email',
                    'password' => 'required|min:8',
                    'dob' => 'required|string',
                    'district_id' => 'required',
                    'type' => 'required',
                    'terms_conditions' => 'required'
                ]);
            }
        } else {
            $message = "Type cannot be null";
            return $this->responseError(404, false, $message);
        }

        DB::beginTransaction();
        try {
            $otp = SMSHelper::generateOTP();

            $user = User::create([
                'name' => $request->first_name . ' ' . $request->last_name,
                'phone' => $request->phone,
                'email' => $request->email ?? null,
                'dob' => $request->dob ?? null,
                'district_id' => $request->district_id ?? null,
                'type' => strtolower($request->type),
                'code' => $citizenCodeNo ?? $consultantCodeNo,
                'password' => Hash::make($request->password),
                'terms_conditions' => $request->terms_conditions,
                'otp_code' => $otp,
            ]);

           $smsMessage = $otp . ' is your LCS verification code ';

            $messageSuccess = SMSHelper::sendSMS($user->phone, $smsMessage);

            $role = Role::where('name', $request->type)->first();
            $user->assignRole($role);

            DB::commit();

            if ($messageSuccess) {
                $message = "Phone Number Verification Code Send Successfully, Check Your Message!";
                return $this->responseSuccess(410, true, $message, $user);
            } else {
                return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, 'Something wrong');
            }

        } catch (QueryException $e) {
            DB::rollBack();
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, $e->getMessage());
        }
    }

    public function registrationWithOTP(Request $request)
    {
        $user = User::where([
            'phone' => $request->phone,
            'otp_code' => $request->otp_code,
            'is_phone_verified' => null
        ])->first();
      //   return $user;
        if ($user) {
            $is_expired_time = $user->updated_at->addMinutes(2);
            $nowTime = Carbon::now();

            if ($is_expired_time >= $nowTime) {
                $user = $user->update([
                    'is_phone_verified' => 1,
                ]);

                if ($user) {
                    $message = "Your Phone Number Verified, And Registration Successfull";
                    return $this->responseSuccess(200, false, $message, []);
                }
            } else {
                $message = "Your OTP time has been expired";
                return $this->responseSuccess(404, false, $message, []);
            }
        } else {
            $message = "Your otp is not correct.";
            return $this->responseError(404, false, $message);
        }
    }

    public function refreshOTP(Request $request)
    {
        $otp = SMSHelper::generateOTP();

        $userExist = User::where('phone', $request->phone)
        ->where('is_phone_verified', null)
        ->first();
        if ($userExist) {

            $smsMessage = $otp . ' is your LCS verification code';
            $messageSuccess = SMSHelper::sendSMS($userExist->phone, $smsMessage);
                $userExist = $userExist->update([
                    'otp_code' => $otp ,
                ]);

            if ($messageSuccess) {
                $message = "Verification Code Send Successfully, Check Your Message!";
                return $this->responseSuccess(200, true, $message, $userExist);
            } else {
                return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, 'Something wrong');
            }
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

    public function me()
    {
        $user = User::with('experiances', 'academics', 'services')->where('id', auth()->user()->id)->first();

        if ($user != null) {
            $data = [
                'user' => $user,
                'role' => $user->getRoleNames(),
                'permissions' => $user->getAllPermissions()->pluck('name')
            ];

            $message = "";
            return $this->responseSuccess(200, true, $message, $data);
        } else {
            $message = "No Data Found";
            return $this->responseError(404, false, $message);
        }
    }
}
