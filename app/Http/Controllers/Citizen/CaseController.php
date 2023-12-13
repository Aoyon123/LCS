<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Http\Helper\FileHandler;
use App\Http\Helper\SendNotification;
use App\Http\Requests\CaseRequest;
use App\Models\LcsCase;
use App\Models\User;
use App\Traits\ResponseTrait;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CaseController extends Controller
{
    use ResponseTrait;

    public function index()
    {
        $data = LcsCase::with(['citizen', 'consultant', 'service:id,title'])->get();
        if (!empty($data)) {
            $message = "Succesfully Data Shown";
            return $this->responseSuccess(200, true, $message, $data);
        } else {
            $message = "Invalid credentials";
            return $this->responseError(403, false, $message);
        }
    }

    public function caseList(Request $request)
    {
        $type = auth()->user()->type;
        $userType = $type === 'citizen' ? 'consultant' : 'citizen';
        $caseData = DB::table('lcs_cases')
            ->where('lcs_cases.' . $type . '_id', auth()->user()->id)
            ->where(['deleted_at' => null])
            ->select(
                'lcs_cases.id as case_id',
                'lcs_cases.consultant_id',
                'lcs_cases.title',
                'lcs_cases.document_file',
                'lcs_cases.rating',
                'lcs_cases.document_link',
                'lcs_cases.case_initial_date',
                'lcs_cases.case_status_date',
                'lcs_cases.description',
                'lcs_cases.case_code',
                'lcs_cases.status',
                'lcs_cases.updated_at',
                'users.name',
                'users.code',
                'users.profile_image',
                'services.id as service_id',
                'services.title as service_title'
            )->join('users', 'lcs_cases.' . $userType . '_id', '=', 'users.id')
            ->join('services', 'lcs_cases.service_id', '=', 'services.id')
            ->orderBy('updated_at', 'DESC');

        $params = $request->all();
        if ($params) {
            foreach ($params as $key => $param) {
                if ($key === 'status') {

                    $caseData = $caseData->where('lcs_cases.status', $param);

                } else if ($request->has('search') && $request->filled('search')) {

                    $searchTerm = $request->search;
                    $caseSearchFields = ['users.name', 'users.code', 'lcs_cases.case_code'];

                    $caseData = $caseData->where(function ($query) use ($caseSearchFields, $searchTerm) {
                        foreach ($caseSearchFields as $userSearchField) {
                            $query->orWhere($userSearchField, 'like', '%' . $searchTerm . '%');
                        }
                    });
                }
            }
        }

        $allCaseData = $caseData->get();

        if ($allCaseData) {
            $message = "Case list data succesfully shown";
            return $this->responseSuccess(200, true, $message, $allCaseData);
        } else {
            $message = "No Data Found";
            return $this->responseError(404, false, $message);
        }
    }

    public function caseListMobile(Request $request)
    {
        $type = auth()->user()->type;
        $userType = $type === 'citizen' ? 'consultant' : 'citizen';

        $caseData = DB::table('lcs_cases')
            ->where('lcs_cases.' . $type . '_id', auth()->user()->id)
            ->where(['deleted_at' => null])
            ->select(
                'lcs_cases.id as case_id',
                'lcs_cases.consultant_id',
                'lcs_cases.title',
                'lcs_cases.document_file',
                'lcs_cases.rating',
                'lcs_cases.document_link',
                'lcs_cases.case_initial_date',
                'lcs_cases.case_status_date',
                'lcs_cases.description',
                'lcs_cases.case_code',
                'lcs_cases.status',
                'lcs_cases.updated_at',
                'users.name',
                'users.code as userCode',
                'users.profile_image',
                'services.id as service_id',
                'services.title as service_title'
            )->join('users', 'lcs_cases.' . $userType . '_id', '=', 'users.id')
            ->join('services', 'lcs_cases.service_id', '=', 'services.id')
            ->orderBy('updated_at', 'DESC');

        $params = $request->all();
        if ($params) {
            foreach ($params as $key => $param) {
                if ($key === 'status') {

                    $caseData = $caseData->where('lcs_cases.status', $param);

                } else if ($request->has('search') && $request->filled('search')) {

                    $searchTerm = $request->search;
                    $caseSearchFields = ['users.name', 'users.code', 'lcs_cases.case_code'];

                    $caseData = $caseData->where(function ($query) use ($caseSearchFields, $searchTerm) {
                        foreach ($caseSearchFields as $userSearchField) {
                            $query->orWhere($userSearchField, 'like', '%' . $searchTerm . '%');
                        }
                    });
                }
            }
        }

        $allCaseData = $caseData->get();

        if ($caseData) {
            $message = "Case list data succesfully shown";
            return $this->responseSuccess(200, true, $message, $caseData);
        } else {
            $message = "No Data Found";
            return $this->responseError(404, false, $message);
        }
    }

    // public function caseList()
    // {
    //     $user = auth()->user();
    //     $type = $user->type;
    //     $userType = $type === 'citizen' ? 'consultant' : 'citizen';
    //     // return $userType;
    //     // $case = LcsCase::with('user', 'service')->find(4);
    //     // return $case;
    //     $caseData = LcsCase::where($type . '_id', $user->id)
    //         ->whereNull('deleted_at')
    //         ->with([
    //             'user' => function ($query) use ($userType) {
    //                 $query->select('id', 'name', 'code', 'profile_image');
    //             },
    //             'service' => function ($query) {
    //                 $query->select('id', 'title');
    //             },
    //         ])
    //         ->select(
    //             'id as case_id',
    //             'consultant_id',
    //             'title',
    //             'document_file',
    //             'rating',
    //             'document_link',
    //             'case_initial_date',
    //             'case_status_date',
    //             'description',
    //             'case_code',
    //             'status'
    //         )
    //         ->orderBy('case_id', 'DESC')
    //         ->get();

    //     if ($caseData->isEmpty()) {
    //         $message = "No Data Found";
    //         return $this->responseError(404, false, $message);
    //     }

    //     $message = "Case list data successfully shown";
    //     return $this->responseSuccess(200, true, $message, $caseData);
    // }

    public function adminCaseList($type, $user_id)
    {
        $userType = $type === 'citizen' ? 'consultant' : 'citizen';
        $caseData = DB::table('lcs_cases')
            ->where('lcs_cases.' . $type . '_id', $user_id)
            ->where('deleted_at', null)
            ->select(
                'lcs_cases.id',
                'lcs_cases.title',
                'lcs_cases.document_file',
                'lcs_cases.document_link',
                'lcs_cases.case_initial_date',
                'lcs_cases.case_status_date',
                'lcs_cases.description',
                'lcs_cases.case_code',
                'lcs_cases.rating',
                'lcs_cases.status',
                'lcs_cases.created_at',
                'lcs_cases.updated_at',
                'users.name',
                'users.code',
                'users.profile_image',
                'services.title as service_title'
            )->join('users', 'lcs_cases.' . $userType . '_id', '=', 'users.id')
            ->join('services', 'lcs_cases.service_id', '=', 'services.id')
            ->orderBy('id', 'DESC')->get();

        if ($caseData) {
            $message = "Consultation list data succesfully shown";
            return $this->responseSuccess(200, true, $message, $caseData);
        } else {
            $message = "No Data Found";
            return $this->responseError(404, false, $message);
        }
    }

    public function store(Request $request)
    {
        try {
            $consultantData = User::where(['id' => $request->consultant_id])->first();
            $case = LcsCase::where('citizen_id', auth()->user()->id)
                ->where('consultant_id', $request->consultant_id)
                ->latest()->first();

            $case_file_path = null;

            $now = Carbon::now();
            $case_code = $now->format('Hsu');

            if ($request->document_file) {
                $extension = '';
                $file_parts = explode(";base64,", $request->document_file);
                $extension_part = $file_parts[0];

                if (str_contains($extension_part, 'text/plain')) {
                    $extension = '.txt';
                } elseif (str_contains($extension_part, 'application/pdf')) {
                    $extension = '.pdf';
                } elseif (str_contains($extension_part, 'application/msword')) {
                    $extension = '.doc';
                } elseif (str_contains($extension_part, 'image')) {
                    $extension = '.png';
                } else {
                    $message = "This type of file not accepted.";
                    return $this->responseError(404, false, $message);
                }
                if (isset($file_parts[1])) {
                    $case_file_path = FileHandler::uploadFile($request->document_file, $extension, $case_code, 'caseFile');
                }
            }

            $request->validate([
                'service_id' => 'required',
                'title' => 'required|string',
                'document_link' => 'nullable|url',
                'document_file' => 'nullable',
                'description' => 'nullable',
            ]);

            $userAgent = $request->header('User-Agent');

            if (strpos($userAgent, 'Mobile') !== false) {
                $deviceLog = 1;
            } else {
                $deviceLog = 2;
            }

            $data = LcsCase::create([
                'service_id' => $request->service_id,
                'citizen_id' => auth()->user()->id,
                'consultant_id' => $request->consultant_id,
                'title' => $request->title,
                'status' => 0,
                'document_link' => $request->document_link ?? '',
                'rating' => $request->rating,
                'description' => $request->description ?? '',
                'case_initial_date' => Carbon::now()->toDateTimeString(),
                'case_status_date' => $request->case_status_date,
                'consultant_review_comment' => $request->consultant_review_comment,
                'citizen_review_comment' => $request->citizen_review_comment,
                'case_code' => $case_code,
                'document_file' => $case_file_path ?? '',
                'device_log' => $deviceLog ?? null,
            ]);

            // $deviceToken = ['device_token' => User::where('id', $data->consultant_id)->first()->device_token];
            // // $deviceToken = ['fj_yFKzTTlKDnS2Ky03yyW:APA91bFtzXG2Vz7pv_UrMuTL2M-Bl4qP-W9sXPiEy7ElVwny4Ddh5o3DUE7pTmxCR4R3QKjCb7T_4lirviGkfnXlN40UM4KFedZkJQ8peYk1M9n84PkQ7CXQSoCkPQAQEZtKeUDs776a'];

            // // Fetch user data based on the citizen_id from the database
            // $userData = User::where('id', $data->citizen_id)->select('name')->first();

            // // Create a new instance of the SendNotification class
            // $sendNotification = new SendNotification();

            // // Check if title and device token and userData are present in the request
            // if (!empty($request->title) && $deviceToken['device_token'] != null && !empty($userData)) {
            //     // Set the FCM token, title, and body based on the status
            //     $FcmToken = [$deviceToken['device_token']];
            //     $title = $userData->name;
            //     $body = $request->title;

            //     $sendNotification->sendNotification($request, $FcmToken, $title, $body);
            // }

            $message = "Consultation Created Successfull";
            // Check if the response status is 200
            if ($this->responseSuccess(200, true, $message, $data)) {
                $deviceToken = ['device_token' => User::where('id', $data->consultant_id)->first()->device_token];

                // Fetch user data based on the citizen_id from the database
                $userData = User::where('id', $data->citizen_id)->select('name')->first();

                // Create a new instance of the SendNotification class
                $sendNotification = new SendNotification();

                // Check if title and device token and userData are present in the request
                if (!empty($request->title) && $deviceToken['device_token'] != null && !empty($userData)) {
                    // Set the FCM token, title, and body based on the status
                    $FcmToken = [$deviceToken['device_token']];
                    $title = $userData->name;
                    $body = $request->title;

                    $sendNotification->sendNotification($FcmToken, $title, $body);
                }
            }

            return $this->responseSuccess(200, true, $message, $data);
        } catch (QueryException $e) {
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, $e->getMessage());
        }
    }

    public function update(CaseRequest $request)
    {

        $caseData = LcsCase::findOrFail($request->id);
        // return $caseData;
        try {
            if ($caseData) {

                $deviceToken = ['device_token' => User::where('id', $caseData->citizen_id)->first()->device_token];
                // $deviceToken = ['fj_yFKzTTlKDnS2Ky03yyW:APA91bFtzXG2Vz7pv_UrMuTL2M-Bl4qP-W9sXPiEy7ElVwny4Ddh5o3DUE7pTmxCR4R3QKjCb7T_4lirviGkfnXlN40UM4KFedZkJQ8peYk1M9n84PkQ7CXQSoCkPQAQEZtKeUDs776a'];

                // Fetch user data based on the consultant_id from the database
                $userData = User::where('id', $caseData->citizen_id)->select('name')->first();

                // Create a new instance of the SendNotification class
                $sendNotification = new SendNotification();

                // Update the relevant fields in the $caseData object based on the incoming request
                $caseData->update([
                    'status' => $request->status ?? $caseData->status,
                    'consultant_review_comment' => $request->consultant_review_comment ?? $caseData->consultant_review_comment,
                    'case_status_date' => Carbon::now()->toDateTimeString(),
                    'rating' => $request->rating ?? $caseData->rating,
                    'citizen_review_comment' => $request->citizen_review_comment ?? $caseData->citizen_review_comment,
                ]);

                // Check if the response is successful (status 200)
                $message = "This Consultation data has been updated";
                if ($this->responseSuccess(200, true, $message, $caseData)) {

                    if ($request->status == 2) {
                        // when $request has 2 then update the case_complete_date
                        $caseData->update(['case_complete_date' => Carbon::now()->addHours(72)->toDateTimeString()]);
                    }
                    // Check if status and device token are present in the request
                    if (!empty($request->status) && $deviceToken['device_token'] != null) {
                        // Set the FCM token, title, and body based on the status
                        // $FcmToken = $deviceToken;

                        $FcmToken = [$deviceToken['device_token']];

                        $title = $userData->name;
                        if ($request->status == 1) {
                            $body = 'আপনার আবেদনটি অপেক্ষমাণ থেকে চলমান করা হয়েছে।';
                        }
                        if ($request->status == 2) {
                            $body = 'আপনার আবেদনটি চলমান থেকে নিস্পন্ন করা হয়েছে। আগামী ৭২ ঘণ্টা পর্যন্ত পরামর্শকের সাথে সমস্যা সম্পর্কিত আলোচনা করতে পারবেন।';
                        }
                        if ($request->status == 3) {
                            $body = 'আপনার আবেদনটি অপেক্ষমাণ থেকে বাতিল করা হয়েছে। অনুগ্রহপূর্বক সঠিক তথ্য দিয়ে আবার আবেদন করুন।';
                        }

                        $sendNotification->sendNotification($FcmToken, $title, $body);
                    }
                }

                // // update "case_complete_date" field based on case completion
                // if ($request->status == 2) {
                //     // $message = "This Consultation data has been updated";
                //     // if ($this->responseSuccess(200, true, $message, $caseData)) {
                //         $caseData->update(['case_complete_date' => Carbon::now()->toDateTimeString()]);
                //     //}
                // }

                // Calculate the average rating for the consultant
                if ($request->rating) {
                    $consultant_id = $caseData->consultant_id;
                    $averageRating = LcsCase::where(['consultant_id' => $consultant_id, 'status' => 2])
                        ->avg('rating');

                    $totalRating = LcsCase::where([
                        'consultant_id' => $consultant_id,
                        'status' => 2,
                    ])->whereNotNull('rating')->count('rating');

                    $roundRating = round($averageRating, 1);
                    User::find($consultant_id)->update(['rates' => $roundRating, 'totalRating' => $totalRating ?? 1]);
                }

                $caseUpdateData = $caseData->orderByDesc('id');
                $message = "This Consultation data has been updated";

                return $this->responseSuccess(200, true, $message, $caseData);
            }
        } catch (QueryException $e) {
            // DB::rollBack();
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $lcsCase = LcsCase::findOrFail($id);
            $lcsCase->delete();

            $message = "Consultation Deleted Succesfully";
            DB::commit();
            return $this->responseSuccess(200, true, $message, []);
        } catch (QueryException $e) {
            DB::rollBack();
        }
    }

    // $service = DB::table('services')->get();
    public function ConsultantServices($id)
    {
        DB::beginTransaction();
        try {
            $services = [];
            $user = User::with('services:id,title')->where('id', $id)->first();
            if ($user) {
                $services = $user['services'];
            }
            // return $user;
            DB::commit();
            $message = "Consultant Services Shown Successfull";
            return $this->responseSuccess(200, true, $message, $services);
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, $e->getMessage());
        }
    }

    public function consultantRating($id)
    {
        DB::beginTransaction();
        try {
            $rating = LcsCase::select(DB::raw('count(rating) as total'))->where('consultant_id', $id)->completed()->first();
            // return $rating;
            DB::commit();
            $message = "Consultant Services Shown Successfull";
            return $this->responseSuccess(200, true, $message, $rating);
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, $e->getMessage());
        }
    }

    // public function statusUpdate(Request $request)
    // {
    //     DB::beginTransaction();
    //     try {
    //         $case = LcsCase::where('id', $request->id)->first();
    //         if ($case) {
    //             $case->update([
    //                 'status' => $request->status,
    //             ]);
    //             if ($request->status == 1) {
    //                 $Status = 'inprogress';
    //             } else if ($request->status == 2) {
    //                 $Status = 'cancel';
    //             } else {
    //                 $Status = 'complete';
    //             }

    //             $message = "This " . $case->case_code . " status " . $Status;
    //             DB::commit();
    //             return $this->responseSuccess(200, true, $message, $case);
    //         } else {
    //             $message = "Not Found Data";
    //             return $this->responseError(404, false, $message);

    //         }
    //     } catch (QueryException $e) {
    //         DB::rollBack();
    //     }
    // }

    public function caseDetailsInfo($id)
    {
        $type = auth()->user()->type;
        $type = $type === 'citizen' ? 'consultant' : 'citizen';

        $caseData = DB::table('lcs_cases')
            ->where('lcs_cases.id', $id)
            ->select(
                'lcs_cases.id',
                'lcs_cases.title',
                'lcs_cases.case_code',
                'lcs_cases.citizen_id',
                'lcs_cases.consultant_id',
                'lcs_cases.document_file',
                'lcs_cases.document_link',
                'lcs_cases.status',
                'lcs_cases.rating as case_rate',
                'lcs_cases.case_initial_date',
                'lcs_cases.case_status_date',
                'lcs_cases.description',
                'lcs_cases.citizen_review_comment',
                'lcs_cases.consultant_review_comment',
                'users.name',
                'users.profile_image',
                'users.rates',
                'services.title as service',
            )->join('users', 'lcs_cases.' . $type . '_id', '=', 'users.id')
            ->join('services', 'lcs_cases.service_id', '=', 'services.id')
            ->first();

        // if ($caseData->citizen_id == auth()->user()->id || $caseData->consultant_id == auth()->user()->id) {
        //     $message = "Consultation details data succesfully shown";
        //     return $this->responseSuccess(200, true, $message, $caseData);
        // } else {
        //     $message = "No Data Found";
        //     return $this->responseError(404, false, $message);
        // }

        if ($caseData) {
            $message = "Consultation details data succesfully shown";
            return $this->responseSuccess(200, true, $message, $caseData);
        } else {
            $message = "No Data Found";
            return $this->responseError(404, false, $message);
        }
    }

    public function adminCaseDetailsInfo($case_id)
    {

        $caseData = LcsCase::with(['citizen', 'consultant', 'service'])
            ->where('lcs_cases.id', $case_id)->first();

        if (Auth::check() && Auth::user()->type == 'admin' || $caseData->citizen_id == Auth::user()->id || $caseData->consultant_id == Auth::user()->id) {
            $message = "Consultation details data succesfully shown";
            return $this->responseSuccess(200, true, $message, $caseData);
        } else {
            $message = "No Data Found";
            return $this->responseError(404, false, $message);
        }
    }

    public function initialCaseList()
    {
        $data = LcsCase::where('status', 0)
        // ->whereDate('updated_at', '<=', date('Y-m-d H:i:s', strtotime('-3 days')))
            ->whereDate('updated_at', '<=', date('Y-m-d H:i:s', strtotime('+5 seconds')))
            ->with(['citizen:id,name,phone,profile_image', 'consultant:id,name,phone,profile_image', 'service:id,title'])
            ->orderBy('id', 'DESC')
            ->get();

        if (!empty($data)) {
            $message = "Succesfully Data Shown";
            return $this->responseSuccess(200, true, $message, $data);
        } else {
            $message = "Invalid credentials";
            return $this->responseError(403, false, $message);
        }
    }

    public function transferableConsultationUpdate(Request $request)
    {

        $consultantData = User::where('id', $request->consultant_id)->first();

        $data = LcsCase::where('status', 0)
            ->whereDate('case_initial_date', '>=', date('Y-m-d H:i:s', strtotime('-3 days')))
            ->with(['citizen:id,name,phone,profile_image', 'consultant:id,name,phone,profile_image', 'service:id,title'])
            ->get();

        if ($data) {
            $caseData = LcsCase::where('id', $request->consultation_id)->first();
            if ($caseData) {
                $caseData->update([
                    'consultant_id' => $request->consultant_id,
                ]);
            }
        }

        // $smsMessage = 'Dear Consultant A New Service Request Admin Forwaded to You';
        // $messageSuccess = SMSHelper::sendSMS($consultantData->phone, $request->message);

        if (!empty($caseData)) {
            $message = "This Consultation Transfer successfully";
            return $this->responseSuccess(200, true, $message, $caseData);
        } else {
            $message = "Invalid credentials";
            return $this->responseError(403, false, $message);
        }
    }

    public function citizenRating()
    {
        $data = DB::table('lcs_cases')
            ->select(
                DB::raw('ROUND(AVG(CASE WHEN status = 2 THEN rating ELSE NULL END), 1) AS average_rating'),
                DB::raw('SUM(CASE WHEN status = 2 AND rating = 5 THEN 1 ELSE 0 END) AS count_5_rating'),
                DB::raw('SUM(CASE WHEN status = 2 AND rating = 4 THEN 1 ELSE 0 END) AS count_4_rating'),
                DB::raw('SUM(CASE WHEN status = 2 AND rating = 3 THEN 1 ELSE 0 END) AS count_3_rating'),
                DB::raw('SUM(CASE WHEN status = 2 AND rating = 2 THEN 1 ELSE 0 END) AS count_2_rating'),
                DB::raw('SUM(CASE WHEN status = 2 AND rating = 1 THEN 1 ELSE 0 END) AS count_1_rating'))
            ->where('status', 2)
            ->first();

        $data->count_5_rating = intval($data->count_5_rating);
        $data->count_4_rating = intval($data->count_4_rating);
        $data->count_3_rating = intval($data->count_3_rating);
        $data->count_2_rating = intval($data->count_2_rating);
        $data->count_1_rating = intval($data->count_1_rating);

        if (!empty($data)) {
            $message = "Succesfully Data Shown";
            return $this->responseSuccess(200, true, $message, $data);
        } else {
            $message = "Invalid credentials";
            return $this->responseError(403, false, $message);
        }
    }

    public function serviceWiseConsultant(Request $request, $serviceId)
    {
        $consultant = User::select('id', 'name', 'fee')
            ->status()
            ->approval()
            ->consultant();

        // Filter consultants based on the service ID
        $consultant = $consultant->whereHas('services', function ($q) use ($serviceId) {
            $q->where('services.id', $serviceId);
        });

        $consultantList = $consultant->get();

        if (!empty($consultantList)) {
            $message = "Succesfully consultant list data shown";
            return $this->responseSuccess(200, true, $message, $consultantList);

        }
    }

    public function pushNotification(Request $request)
    {
        $url = 'https://fcm.googleapis.com/fcm/send';

        // Replace with your actual FCM tokens
        $registrationTokens = ['ce8zVo0TR0SCkvQZopQ5Bn:APA91bFmriDbzNa2uB3kOgKp4hx2yWpc3I27nRtieN7_J6QjHuPI1Yb1h7_7uhvsUi-ztBmkybdbZ0eemsAMs45rAWGxsHB5jw3xC8e_2j0XIifrapPugSfnDcNpqW8zqHntakRL4mw0'];

        $serverKey = 'AAAAHIIT-Uo:APA91bE7DqaZnkugFtk7o7VjxkgrwvZbaO-21hmVy96Jn4XdGy9s8mvD-zgEV7JHq6-5vmWL8h5-r3x5dGRxoLSPf9pjNiD8oa2gUFvW07BqXhZ5YnwTS9Vqgpfl8gXMhfyHH8p-83TC';

        $data = [
            'registration_ids' => $registrationTokens,
            'notification' => [
                'title' => 'Vumiseba',
                'body' => 'Push Notification',
            ],
        ];

        $dataString = json_encode($data);

        $headers = [
            'Content-Type: application/json',
            'Authorization:key=' . $serverKey,
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

        $result = curl_exec($ch);
        if ($result === false) {
            die('Oops! FCM Send Error: ' . curl_error($ch));
        }

        curl_close($ch);
        // dd($result);

    }

}
