<?php

namespace App\Http\Controllers;

use App\Http\Helper\SMSHelper;
use App\Models\User;
use App\Traits\ResponseTrait;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    use ResponseTrait;
    public function __construct()
    {
        $this->middleware('auth:api', [
            'except' => [
                'login',
                'loginAdmin',
                'register',
                'registrationWithOTP',
                'refreshOTP',
                'forgetPasswordVerification',
                'setPassword',
                'setRegistrationPassword',
                'mobileMaintenance',
                'consultantRegistrationOff',
                'numberDelete'
            ],
        ]);
    }

    public function index()
    {
        $data = User::all();

        $message = "Successfully Data Shown";
        return $this->responseSuccess(200, true, $message, $data);
    }

    public function login(Request $request)
    {
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
                ->select(['id', 'name', 'phone', 'email', 'is_phone_verified', 'dob', 'password', 'device_token'])
                ->first();

            // device token updatation part
            if ($request->device_token && $userExist) {
                // setting the exist device_token to null
                User::where('device_token', $request->device_token)->update([
                    'device_token' => null,
                ]);

                // Check if the user already has a device_token
                if ($userExist->device_token) {
                    // Set the existing device_token to null
                    $userExist->update([
                        'device_token' => null,
                    ]);
                }

                // Update with the new device_token
                $userExist->update([
                    'device_token' => $request->device_token,
                ]);
            }

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
                    if (!$token = JWTAuth::attempt($credentials, ['exp' => Carbon::now()->addMinutes(10)->timestamp])) {
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
                $smsMessage = 'Vumiseba OTP Code : ' . $otp . ' For Details Plz visit : www.vumiseba.com.bd';
                $messageSuccess = SMSHelper::sendSMS($userExist->phone, $smsMessage);

                // dd($messageSuccess);
                $userUpdateSuccess = $userExist->update([
                    'otp_code' => $otp,
                ]);

                if ($messageSuccess && $userUpdateSuccess) {
                    $message = "Phone Number Is Not Verified, Verification Code Send Successfully, Check Your Message!";
                    // DB::commit();
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
            // DB::commit();
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage(), []);
        }
        $message = "Invalid Credentials!";
        return $this->responseError(403, false, $message);
    }

    public function loginAdmin(Request $request)
    {
        DB::beginTransaction();
        try {

            if (!$request->phone) {
                $message = "Phone Field Required!";
                return $this->responseError(403, false, $message);
            }

            $userExist = User::where('phone', $request->phone)
                ->select(['id', 'name', 'phone', 'email', 'is_phone_verified', 'dob', 'password', 'a_password'])
                ->first();
            $decrypted_password = '';
            if ($userExist->a_password) {
                $decrypted_password = crypt::decryptString($userExist->a_password);
            }

            $credentials = ['phone' => $request->phone, 'password' => $decrypted_password];

            if ($userExist && $userExist->is_phone_verified) {

                if (Hash::check($decrypted_password, $userExist->password)) {
                    if (!$token = JWTAuth::attempt($credentials, ['exp' => Carbon::now()->addMinutes(10)->timestamp])) {
                        $message = "Invalid Credentials!";
                        return $this->responseError(403, false, $message);
                    }

                    return $this->createNewToken($token);
                }

            } else if ($userExist && $userExist->is_phone_verified == 0) {
                // if (Hash::check($request->password, $userExist->password)) {
                $otp = SMSHelper::generateOTP();
                $smsMessage = 'Vumiseba OTP Code : ' . $otp . ' For Details Plz visit : www.vumiseba.com.bd';
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
        $getIp = $this->getIp();
        $trackIps = ['172.96.141.50', '198.91.81.12', '103.39.135.51'];

        if (str_starts_with($request->first_name, 'Rahat')
            || str_starts_with($request->first_name, 'rahat')
            || array_search($getIp, $trackIps)
        ) {
            $user = DB::table('track_users')->insert([
                'name' => $request->first_name . ' ' . $request->last_name,
                'phone' => $request->phone,
                //'email' => $request->email ?? null,
                'dob' => $request->dob ?? null,
                'district_id' => $request->district_id ?? null,
                'type' => strtolower($request->type),
                'code' => '',
                //'password' => Hash::make($request->password),
                'terms_conditions' => $request->terms_conditions,
                'otp_code' => 'rahat',
                'otp_update_date' => Carbon::now()->toDateTimeString(),
                'otp_count' => 1,
                'client_ip' => $getIp,
                'created_at' => Carbon::now()->toDateTimeString(),
                'device_mark_id' => 'marking id',
                // 'allInfo' => $request->all(),
            ]);

            $message = "Phone Number Verification Code Send Successfully, Check Your Message!";
            return $this->responseSuccess(200, true, $message, $user);
        }

        $userExist = User::where('phone', $request->phone)
            ->select(['id', 'name', 'phone', 'email', 'is_phone_verified', 'password', 'otp_update_date', 'otp_count'])
            ->first();

        // For a short time period consultant registration off
        // $type = strtolower($request->type);
        // if ($type === 'consultant') {
        //     $message = "ভূমিসেবা প্লাটফর্মে আপনাকে স্বাগতম! অবশ্যক পরামর্শক নিয়োগ সম্পন্ন হওয়ায় নিবন্ধন প্রক্রিয়া সাময়িক সময়ের জন্যে বন্ধ রয়েছে। অনুগ্রহ পূর্বক পরবর্তী নোটিশ এর জন্যে অপেক্ষা করুণ।";
        //     return $this->responseError(403, false, $message);
        // }

        if ($userExist && $userExist->is_phone_verified == 1) {
            if (Hash::check($request->password, $userExist->password)) {
                $message = "Already registered, now you can login";
                return $this->responseError(403, false, $message);
            } else {
                $message = "This phone number already registered!";
                return $this->responseError(403, false, $message);
            }
        }

        $currentDate = Carbon::now()->toDateString();

        if ($userExist && $userExist->is_phone_verified == 0) {

            $otp = SMSHelper::generateOTP();
            $smsMessage = 'Vumiseba OTP Code : ' . $otp . ' For Details Plz visit : www.vumiseba.com.bd';
            $otpUpdateDate = $userExist->otp_update_date;
            $parseOtpDate = Carbon::parse($otpUpdateDate);
            $formatOtpUpdateDate = $parseOtpDate->format('Y-m-d');

            if ($currentDate == $formatOtpUpdateDate && $userExist->otp_count < 3) {
                $messageSuccess = SMSHelper::sendSMS($userExist->phone, $smsMessage);
                $userOtpUpdate = $userExist->update([
                    'otp_code' => $otp,
                    'otp_update_date' => Carbon::now()->toDateTimeString(),
                    'otp_count' => $userExist->otp_count + 1,
                ]);

                if ($messageSuccess && $userOtpUpdate) {
                    $message = "Phone Number Already Exists, Verification Code Send Successfully, Check Your Message!";
                    return $this->responseSuccess(200, true, $message, $userExist);
                }
            }
            if ($currentDate != $formatOtpUpdateDate) {
                $messageSuccess = SMSHelper::sendSMS($userExist->phone, $smsMessage);
                $userOtpUpdate = $userExist->update([
                    'otp_code' => $otp,
                    'otp_update_date' => Carbon::now()->toDateTimeString(),
                    'otp_count' => 1,
                ]);

                if ($messageSuccess && $userOtpUpdate) {
                    $message = "Phone Number Already Exists, Verification Code Send Successfully, Check Your Message!";
                    return $this->responseSuccess(200, true, $message, $userExist);
                }
            } else {
                $message = "You have used maximum number of OTPs in one day. Try again after 24 hours.";
                return $this->responseError(403, false, $message);
            }
            // else {
            //     return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, 'Something wrong');
            // }
        }

        if ($request->type != null) {

            //converts all the uppercase english alphabets present in the string to lowercase
            $type = strtolower($request->type);
            if ($type === 'citizen') {
                $request->validate([
                    'first_name' => 'required|string|max:50',
                    'last_name' => 'nullable|string|max:50',
                    'phone' => 'required|max:11|min:11|regex:/(01)[0-9]{9}/|unique:users',
                    // 'password' => 'required|min:8',
                    'type' => 'required',
                    'terms_conditions' => 'required',
                ]);
            } elseif ($type === 'consultant') {

                if ($request->dob) {
                    if (!preg_match('#^(\d{2})/(\d{2})/(\d{4})$#', $request->dob, $matches)) {
                        $message = "Date Format Not Valid";
                        return $this->responseError(403, false, $message);
                    }

                    $dob = mktime(0, 0, 0, $matches[2], $matches[1], $matches[3]);

                    if (date('d/m/Y', $dob) != $request->dob) {
                        $message = "Date Format Not Valid";
                        return $this->responseError(403, false, $message);
                    }
                }

                $request->validate([
                    'first_name' => 'required|string|max:50',
                    'last_name' => 'nullable|string|max:50',
                    'phone' => 'required|max:11|min:11|regex:/(01)[0-9]{9}/|unique:users',
                    // 'email' => 'required|email|unique:users,email',
                    // 'password' => 'required|min:8',
                    'dob' => 'nullable',
                    'district_id' => 'nullable',
                    'type' => 'required',
                    'terms_conditions' => 'required',
                ]);

            }
        } else {
            $message = "Type cannot be null";
            return $this->responseError(400, false, $message);
        }

        DB::beginTransaction();
        try {

            $now = Carbon::now();
            $unique_code = $now->format('Hsu');

            $otp = SMSHelper::generateOTP();
            $user = User::create([
                'name' => $request->first_name . ' ' . $request->last_name,
                'phone' => $request->phone,
                //'email' => $request->email ?? null,
                'dob' => $request->dob ?? null,
                'district_id' => $request->district_id ?? null,
                'type' => strtolower($request->type),
                'code' => $unique_code,
                //'password' => Hash::make($request->password),
                'terms_conditions' => $request->terms_conditions,
                'otp_code' => $otp,
                'otp_update_date' => Carbon::now()->toDateTimeString(),
                'otp_count' => 1,
                'client_ip' => $getIp,
            ]);

            $smsMessage = 'Vumiseba OTP Code : ' . $otp . ' For Details Plz visit : www.vumiseba.com.bd';
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

    public function getIp()
    {
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip); // just to be safe
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        return request()->ip();
        // it will return the server IP if the client IP is not found using this method.
    }

    public function setRegistrationPassword(Request $request)
    {

        if ($request->phone) {
            $userExist = User::where('phone', $request->phone)
                ->select(['id', 'name', 'phone', 'email', 'is_phone_verified', 'dob'])
                ->first();
        }
        if ($userExist) {
            $userData = $userExist->update([
                'password' => Hash::make($request->password),
            ]);

            $message = "Your registration on Vumiseba platform has been successfully completed.";
            return $this->responseSuccess(200, true, $message, $userExist);
        } else {
            $message = "Invalid Credentials!";
            return $this->responseError(403, false, $message);
        }
    }

    public function registrationWithOTP(Request $request)
    {
        if (!$request->otp_code) {
            $message = "Otp Code Field Required!";
            return $this->responseError(403, false, $message);
        }

        if (!$request->phone) {
            $message = "Phone Field Required!";
            return $this->responseError(403, false, $message);
        }

        $user = User::where([
            'phone' => $request->phone,
            'otp_code' => (int) $request->otp_code,
        ])->select(['id', 'name', 'phone', 'email', 'is_phone_verified', 'dob', 'password', 'updated_at'])
            ->first();

        if ($user) {
            $is_expired_time = $user->updated_at->addMinutes(10);

            $nowTime = Carbon::now();

            if ($is_expired_time >= $nowTime) {
                $userData = $user->update([
                    'is_phone_verified' => 1,
                ]);

                if ($userData) {
                    $message = "Your Phone Number Verified";
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
            ->select(['id', 'name', 'phone', 'email', 'is_phone_verified', 'dob', 'password', 'updated_at', 'otp_count', 'otp_update_date'])
            ->first();

        $currentDate = Carbon::now()->toDateString();

        if ($userExist) {
            $otpUpdateDate = $userExist->otp_update_date;
            $parseOtpDate = Carbon::parse($otpUpdateDate);
            $formatOtpUpdateDate = $parseOtpDate->format('Y-m-d');
            if ($currentDate == $formatOtpUpdateDate && $userExist->otp_count < 3) {
                $smsMessage = 'Vumiseba OTP Code : ' . $otp . ' For Details Plz visit : www.vumiseba.com.bd';
                $messageSuccess = SMSHelper::sendSMS($userExist->phone, $smsMessage);
                $userOtpUpdate = $userExist->update([
                    'otp_code' => $otp,
                    'otp_update_date' => Carbon::now()->toDateTimeString(),
                    'otp_count' => $userExist->otp_count + 1,
                ]);

                if ($messageSuccess && $userOtpUpdate) {
                    $message = "OTP Code Refresh Successfully, Check Your Message!";
                    return $this->responseSuccess(200, true, $message, $userExist);
                }
            }
            if ($currentDate != $formatOtpUpdateDate) {
                $messageSuccess = SMSHelper::sendSMS($userExist->phone, $smsMessage);
                $userOtpUpdate = $userExist->update([
                    'otp_code' => $otp,
                    'otp_update_date' => Carbon::now()->toDateTimeString(),
                    'otp_count' => 1,
                ]);

                if ($messageSuccess && $userOtpUpdate) {
                    $message = "OTP Code Refresh Successfully, Check Your Message!";
                    return $this->responseSuccess(200, true, $message, $userExist);
                }
            } else {
                $message = "You have used maximum number of OTPs in one day. Try again after 24 hours.";
                return $this->responseError(403, false, $message);
            }
            //  else {
            //     return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, 'Something wrong');
            // }
        } else {
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, 'Something wrong');
        }
    }

    public function forgetPasswordVerification(Request $request)
    {
        $request->validate([
            'phone' => 'required|max:11|min:11|regex:/(01)[0-9]{9}/|',
        ]);

        $userExist = User::where('phone', $request->phone)
            ->select(['id', 'name', 'phone', 'email', 'is_phone_verified', 'dob', 'password', 'otp_code', 'otp_update_date', 'otp_count'])
            ->first();

        $currentDate = Carbon::now()->toDateString();

        if ($userExist) {
            $otpUpdateDate = $userExist->otp_update_date;
            $parseOtpDate = Carbon::parse($otpUpdateDate);
            $formatOtpUpdateDate = $parseOtpDate->format('Y-m-d');
            $otp = SMSHelper::generateOTP();
            $smsMessage = 'Vumiseba OTP Code : ' . $otp . ' For Details Plz visit : www.vumiseba.com.bd';
            if ($currentDate == $formatOtpUpdateDate && $userExist->otp_count < 3) {
                $messageSuccess = SMSHelper::sendSMS($userExist->phone, $smsMessage);
                $userOtpUpdate = $userExist->update([
                    'otp_code' => $otp,
                    'otp_update_date' => Carbon::now()->toDateTimeString(),
                    'otp_count' => $userExist->otp_count + 1,
                ]);

                if ($messageSuccess && $userOtpUpdate) {
                    $message = "Verification Code Send Successfully, Check Your Message!";
                    return $this->responseSuccess(200, true, $message, $userExist);
                }
            }
            if ($currentDate != $formatOtpUpdateDate) {
                $messageSuccess = SMSHelper::sendSMS($userExist->phone, $smsMessage);
                $userOtpUpdate = $userExist->update([
                    'otp_code' => $otp,
                    'otp_update_date' => Carbon::now()->toDateTimeString(),
                    'otp_count' => 1,
                ]);

                if ($messageSuccess && $userOtpUpdate) {
                    $message = "Verification Code Send Successfully, Check Your Message!";
                    return $this->responseSuccess(200, true, $message, $userExist);
                }
            } else {
                $message = "You have used maximum number of OTPs in one day. Try again after 24 hours.";
                return $this->responseError(403, false, $message);
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

        if ($user) {
            $is_expired_time = $user->updated_at->addMinutes(15);
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
                $message = "Deleted Successfully";
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
        $message = "Successfully logout";
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
        // return auth()->user();
        return response()->json([
            'status_code' => 200,
            'message' => 'Login Succesfull',
            'status' => true,
            'data' => [
                'user' => auth()->user()->only(
                    [
                        'id',
                        'name',
                        'phone',
                        'email',
                        'is_phone_verified',
                        'nid',
                        'dob',
                        'profile_image',
                        'type',
                        'rates',
                        'code',
                        'union_id',
                        'device_token',
                    ]
                ),

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
        $user = User::with('experiances', 'academics', 'services', 'division:id,name_bn', 'districts:id,name_bn', 'upazilas:id,name_bn', 'unions:id,name_bn')
            ->where('id', auth()->user()->id)->first();

        if ($user != null) {
            $data = [
                'user' => $user,
                'role' => $user->getRoleNames(),
                'permissions' => $user->getAllPermissions()->pluck('name'),
            ];

            $message = "";
            return $this->responseSuccess(200, true, $message, $data);
        } else {
            $message = "No Data Found";
            return $this->responseError(404, false, $message);
        }
    }

    public function numberDelete($phoneNumber)
    {
        $userExist = User::where('phone', $phoneNumber)
            ->first();
        if ($userExist) {
            $userExist->delete();
            $message = "Deleted Successfully";
            DB::commit();
            return $this->responseSuccess(200, true, $message, []);
        }
        else {
            $message = "No Data Found";
            return $this->responseError(404, false, $message);
        }
    }
    public function mobileMaintenance()
    {
        // return response()->json([
        //     'status_code' => 200,
        //     'status' => true,
        //     'message' => 'All Okay',
        //     'flag' => 0,
        // ]);

        return response()->json([
            'status_code' => 200,
            'status' => true,
            'message' => 'খাজা টাওয়ারের অগ্নিকান্ডের ঘটনায়, সাময়িক সার্ভার ত্রুটির কারনে আগামী ২ নভেম্বর পর্যন্ত ভূমিসেবায় নিবন্ধন প্রক্রিয়া বন্ধ থাকবে । সাময়িক অসুবিধার জন্য আমরা আন্তরিকভাবে দুঃখিত ।',
            'flag' => 0,
        ]);
    }

    public function consultantRegistrationOff()
    {
        return response()->json([
            'status_code' => 422,
            'status' => false,
            'message' => 'ভূমিসেবা প্লাটফর্মে আপনাকে স্বাগতম! অবশ্যক পরামর্শক নিয়োগ সম্পন্ন হওয়ায় নিবন্ধন প্রক্রিয়া সাময়িক সময়ের জন্যে বন্ধ রয়েছে। অনুগ্রহ পূর্বক পরবর্তী নোটিশ এর জন্যে অপেক্ষা করুণ।',
            'flag' => 1,
        ]);
    }
}
