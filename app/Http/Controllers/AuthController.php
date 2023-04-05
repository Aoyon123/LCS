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
        $this->middleware('auth:api', [
            'except' => [
                'login',
                'register',
                'registrationWithOTP',
                'refreshOTP',
                'forgetPasswordVerification',
                'setPassword'
            ]
        ]);
    }

    public function index()
    {
        $data = User::all();

        $message = "Succesfully Data Shown";
        return $this->responseSuccess(200, true, $message, $data);
    }

    public function login(Request $request)
    {

        DB::beginTransaction();
        try {

            if (!$request->password) {
                $message = "Password Field Required!";
                return $this->responseError(403, false, $message);
            }

            if (!$request->phone) {
                $message = "Phone Field Required!";
                return $this->responseError(403, false, $message);
            }

            $userExist = User::where('phone', $request->phone)
                ->select(['id', 'name', 'phone', 'email', 'is_phone_verified', 'dob', 'password'])
                ->first();

            if ($request->phone) {
                if (preg_match("/(^(\+88|0088)?(01){1}[3456789]{1}(\d){8})$/", $request->phone)) {
                    $credentials = ['phone' => $request->phone, 'password' => $request->password];
                } else {
                    $message = "Your Phone Format Is Not Valid!";
                    return $this->responseError(403, false, $message);
                }
            }

            if ($userExist && $userExist->is_phone_verified) {
                if (Hash::check($request->password, $userExist->password)) {
                    if (!$token = JWTAuth::attempt($credentials, ['exp' => Carbon::now()->addMinutes(2)->timestamp])) {
                        $message = "Invalid Credentials!";
                        return $this->responseError(403, false, $message);
                    }
                    return $this->createNewToken($token);
                } else {
                    $message = "Your Password Not Match!";
                    return $this->responseError(403, false, $message);
                }
            } else if ($userExist && $userExist->is_phone_verified == 0) {
                // if (Hash::check($request->password, $userExist->password)) {
                $otp = SMSHelper::generateOTP();
                $smsMessage = $otp . ' is your LCS verification code';
                $messageSuccess = SMSHelper::sendSMS($userExist->phone, $smsMessage);

                $userUpdateSuccess = $userExist->update([
                    'otp_code' => $otp,
                ]);

                if ($messageSuccess && $userUpdateSuccess) {
                    $message = "Phone Number Is Not Verified, Verification Code Send Successfully, Check Your Message!";
                    DB::commit();
                    $data = [
                        'user' => $userExist,
                    ];
                    return $this->responseSuccess(200, true, $message, $data);
                } else {
                    return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, 'Something wrong');
                }
            } else {
                $message = "Please Enter Your Valid Phone!";
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
        $userExist = User::where('phone', $request->phone)
            ->select(['id', 'name', 'phone', 'email', 'is_phone_verified', 'dob', 'password', 'otp_code'])
            ->first();

        if ($userExist && $userExist->is_phone_verified == 1) {
            if (Hash::check($request->password, $userExist->password)) {
                $message = $userExist->phone . " already registered, now you can login";
                // return $this->responseSuccess(200, true, $message, $userExist);
                return $this->responseError(403, false, $message);
            } else {
                $message = "This phone number already registered!";
                return $this->responseError(403, false, $message);
            }
        }

        if ($userExist && $userExist->is_phone_verified == 0) {
            $otp = SMSHelper::generateOTP();
            $smsMessage = $otp . ' is your LCS verification code';
            $messageSuccess = SMSHelper::sendSMS($userExist->phone, $smsMessage);
            $userOtpUpdate = $userExist->update([
                'otp_code' => $otp,
            ]);

            if ($messageSuccess && $userOtpUpdate) {
                $message = "Phone Number Already Exists, Verification Code Send Successfully, Check Your Message!";
                return $this->responseSuccess(200, true, $message, $userExist);
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
            return $this->responseError(400, false, $message);
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
                return $this->responseSuccess(200, true, $message, $user);
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
        if (!$request->otp_code) {
            $message = "Otp_Code Field Required!";
            return $this->responseError(403, false, $message);
        }

        if (!$request->phone) {
            $message = "Phone Field Required!";
            return $this->responseError(403, false, $message);
        }

        $user = User::where([
            'phone' => $request->phone,
            'otp_code' => (int) $request->otp_code,
            // 'is_phone_verified' => 0
        ])->select(['id', 'name', 'phone', 'email', 'is_phone_verified', 'dob', 'password', 'updated_at'])
            ->first();
        //  return $user;
        if ($user) {
            $is_expired_time = $user->updated_at->addMinutes(2);
            // return
            $nowTime = Carbon::now();

            if ($is_expired_time >= $nowTime) {
                $userData = $user->update([
                    'is_phone_verified' => 1,
                ]);

                if ($userData) {
                    $message = "Your Phone Number Verified, And Registration Successfull";
                    return $this->responseSuccess(200, true, $message, $user);
                }
            } else {
                $message = "Your OTP time has been expired";
                return $this->responseError(404, false, $message);
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
            //  ->where('is_phone_verified', 0)
            ->select(['id', 'name', 'phone', 'email', 'is_phone_verified', 'dob', 'password', 'updated_at', 'otp_code'])
            ->first();
        //  return $userExist;
        if ($userExist) {
            $smsMessage = $otp . ' is your LCS verification code';
            $messageSuccess = SMSHelper::sendSMS($userExist->phone, $smsMessage);

            $userOtpUpdate = $userExist->update([
                'otp_code' => $otp,
            ]);

            if ($messageSuccess && $userOtpUpdate) {
                $message = "OTP Code Refresh Successfully, Check Your Message!";
                return $this->responseSuccess(200, true, $message, $userExist);
            } else {
                return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, 'Something wrong');
            }
        }
    }


    public function forgetPasswordVerification(Request $request)
    {
        $request->validate([
            'phone' => 'required|max:11|min:11|regex:/(01)[0-9]{9}/|',
        ]);

        $userExist = User::where('phone', $request->phone)
            ->select(['id', 'name', 'phone', 'email', 'is_phone_verified', 'dob', 'password', 'otp_code'])
            ->first();

        if ($userExist) {
            $otp = SMSHelper::generateOTP();
            $smsMessage = $otp . ' is your LCS verification code';
            $messageSuccess = SMSHelper::sendSMS($userExist->phone, $smsMessage);

            $userOtpUpdate = $userExist->update([
                'otp_code' => $otp,
            ]);

            if ($messageSuccess && $userOtpUpdate) {
                $message = "Verification Code Send Successfully, Check Your Message!";
                return $this->responseSuccess(200, true, $message, $userExist);
            } else {
                return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, 'Something wrong');
            }
        } else {
            $message = "This Phone Number Is Not Exists!";
            return $this->responseError(400, false, $message);
        }
    }

    public function setPassword(Request $request)
    {
        if (!$request->password) {
            $message = "Password Field Required!";
            return $this->responseError(403, false, $message);
        }
        $request->validate([
            'password' => 'required|min:8',
        ]);
        $user = User::where([
            'phone' => $request->phone,
        ])->select(['id', 'name', 'phone', 'email', 'is_phone_verified', 'dob', 'password', 'updated_at'])
            ->first();
        // return $user;
        if ($user) {
            $is_expired_time = $user->updated_at->addMinutes(3);

            $nowTime = Carbon::now();

            if ($is_expired_time >= $nowTime) {
                $userData = $user->update([
                    'password' => Hash::make($request->password),
                ]);

                if ($userData) {
                    $message = "Your Password Updated Successfully";
                    return $this->responseSuccess(200, true, $message, $user);
                }
            } else {
                $message = "Time Has Been Expired";
                return $this->responseError(404, false, $message);
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
        // $userData=auth()->user()
        return response()->json([
            'status_code' => 200,
            'message' => 'Login Succesfull',
            'status' => true,
            'data' => [
                'user' => auth()->user()
                    ->only([
                        'id',
                        'name',
                        'phone',
                        'email',
                        'is_phone_verified',
                        'dob',
                        'profile_image',
                        'type',
                        'rates',
                        'code'
                    ]),

                'role' => User::where('id', auth()->user()->id)->first()->getRoleNames(),
                'permissions' => User::where('id', auth()->user()->id)->first()
                    ->getAllPermissions()->pluck('name'),
                'access_token' => $token,
                'token_type' => 'bearer',
                // 'expires_in' => auth()->factory()->getTTL() * 120,
                'expires_in' => Carbon::now()->addMinutes(1440),
            ],

        ]);
    }

    public function me()
    {
        $user = User::with('experiances', 'academics', 'services')
            ->where('id', auth()->user()->id)->first();

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
