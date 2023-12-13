<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Helper\SendNotification;
use App\Http\Helper\SMSHelper;
use App\Models\User;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ConsultantController extends Controller
{
    use ResponseTrait;
    // public function index()
    // {
    //     $consultants = User::where('type', 'consultant')->get();
    //     if ($consultants != null) {
    //         $message = "";
    //         return $this->responseSuccess(200, true, $message, $consultants);
    //     } else {
    //         $message = "No Data Found";
    //         return $this->responseError(404, false, $message);
    //     }
    // }

    public function index()
    {
        $consultants = User::where('type', 'consultant')
            ->where('is_phone_verified', 1)
            ->get();

        if ($consultants->isEmpty()) {
            $message = "No Data Found";
            return $this->responseError(404, false, $message);
        }

        $message = "";
        return $this->responseSuccess(200, true, $message, $consultants);
    }

    public function approveConlsultantList()
    {
        $consultants_selected_fields = ['id', 'name', 'phone', 'type', 'profile_image', 'rates', 'schedule', 'serialize', 'fee'];
        $consultant = User::
            select($consultants_selected_fields)
            ->withCount(['consultation as consultationCount'])
            ->approval()->consultant()->status()->get();

        if (!empty($consultant)) {
            $message = "Succesfully Data Shown";
            return $this->responseSuccess(200, true, $message, $consultant);
        } else {
            $message = "Invalid credentials";
            return $this->responseError(403, false, $message);
        }
    }

    public function approvalConsultant(Request $request)
    {
        $consultantData = User::where(['id' => $request->id])->first();

        if ($consultantData) {
            $consultantData->update([
                'approval' => (int) $request->approvalStatus,
                'approved_by' => auth()->user()->id,
            ]);

            if ($request->approvalStatus == 3) {
                $consultantData->update([
                    'active_status' => 0,
                ]);
            }

            if ($request->approvalStatus == 2) {
                $consultantData->update([
                    'active_status' => 0,
                ]);
            }

            $deviceToken = ['device_token' => User::where('id', $consultantData->id)->first()->device_token];

            // if ($deviceToken['device_token'] == null) {
            //     $messageSuccess = SMSHelper::sendSMS($consultantData->phone, $request->message);
            // }

            // $deviceToken = ['fj_yFKzTTlKDnS2Ky03yyW:APA91bFtzXG2Vz7pv_UrMuTL2M-Bl4qP-W9sXPiEy7ElVwny4Ddh5o3DUE7pTmxCR4R3QKjCb7T_4lirviGkfnXlN40UM4KFedZkJQ8peYk1M9n84PkQ7CXQSoCkPQAQEZtKeUDs776a'];

            // Fetch user data
            $userData = $consultantData->name;
            // Create a new instance of the SendNotification class
            $sendNotification = new SendNotification();
            // Check if device token and userData and approvalStatus are present in the request
            if (!empty($userData) && $deviceToken['device_token'] != null && !empty($request->approvalStatus)) {
                // Set the FCM token, title, and body based on the approvalStatus
                $FcmToken = [$deviceToken['device_token']];
                $title = $userData;

                if ($request->approvalStatus == 1) {
                    $body = 'আপনার প্রোফাইলটি অনুমোদন করা হল।';
                }
                if ($request->approvalStatus == 2) {
                    $body = 'আপনার প্রোফাইলটি বাতিল করা হয়েছে পুনরায় আবেদন করুন।';
                }
                if ($request->approvalStatus == 3) {
                    $body = 'আপনার প্রোফাইলটি পুনরায় অনুমোদনের জন্য অপেক্ষা করুন।';
                }

                if ($this->responseSuccess(200, true, 'Success', $consultantData)) {
                    $sendNotification->sendNotification($FcmToken, $title, $body);
                 }
            }

            if ($consultantData) {
                $message = "Consultant Approval Update And Message Send Successfully";
                return $this->responseSuccess(200, true, $message, $consultantData);
            } else {
                return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, 'Something wrong');
            }
        }
    }

    public function adminConsultantInformation(Request $request)
    {
        $data = [];
        $consultacts = User::Consultant()->PhoneVerified();

        $totalConsultantDataCount = User::Consultant()->PhoneVerified()->count();

        $totalApprovedConsulatntCount = User::Consultant()->Status()->PhoneVerified()->Approval()->count();
        $totalWaitingConsultantCount = User::Consultant()->PhoneVerified()->Initial()->count();
        $totalRejectedConsultantCount = User::Consultant()->PhoneVerified()->Rejected()->count();
        $highestRatingConsultantCount = User::Consultant()->PhoneVerified()->Approval()->Status()
            ->where('rates', '>=', '4.0')->count();
        $totalDeactivatedConsultantCount = User::Consultant()->PhoneVerified()->Deactivated()->count();
        $highestPaidConsultantCount = 0;
        $highestConsultationConsultantCount = User::Consultant()->Status()->PhoneVerified()->Approval()->withCount(['consultation as consultationCount'])->orderByDesc('consultationCount')->count();

        $params = $request->all();

        $consultantData = User::PhoneVerified()->orderBy('id', 'ASC');
        if ($params) {
            foreach ($params as $key => $param) {

                if ($key === 'totalConsultant') {

                    $totalConsultantDataCount = $consultantData
                        ->where('users.type', $param)->count();

                    $consultantData = $consultantData
                        ->where('users.type', $param);
                } else if ($key === 'approvedConsultant') {

                    $totalConsultantDataCount = $consultantData
                        ->Consultant()
                        ->where('users.approval', $param)->count();

                    $consultantData = $consultantData
                        ->Consultant()

                        ->where('users.approval', $param);
                } else if ($key === 'waitingConsultant') {

                    $totalConsultantDataCount = $consultantData
                        ->Consultant()

                        ->where('users.approval', $param)->count();

                    $consultantData = $consultantData
                        ->Consultant()

                        ->where('users.approval', $param);
                } else if ($key === 'rejectedConsultant') {

                    $totalConsultantDataCount = $consultantData
                        ->Consultant()
                        ->where('users.approval', $param)->count();

                    $consultantData = $consultantData
                        ->Consultant()
                        ->where('users.approval', $param);
                } else if ($key === 'highestRatingConsultant') {
                    //highestRatingConsultant=4.0
                    $totalConsultantDataCount = $consultantData
                        ->Consultant()->Status()->Approval()
                        ->where('users.rates', '>=', $param)
                        ->count();
                    $consultantData = $consultantData
                        ->Consultant()->Status()->Approval()
                        ->where('users.rates', '>=', $param);

                } else if ($key === 'deactivatedConsultant') {
                    //deactivatedConsultant=3
                    $totalConsultantDataCount = $consultantData
                        ->Consultant()
                        ->where('users.approval', $param)
                        ->count();

                    $consultantData = $consultantData
                        ->Consultant()
                        ->where('users.approval', $param);

                } else if ($key === 'highestConsultationConsultant') {

                    $totalConsultantDataCount = 15;
                    // $consultantData
                    //     ->Consultant()->Status()->PhoneVerified()->Approval()->withCount(['consultation as consultationCount'])->orderByDesc('consultationCount')->count();
                    $consultantData = $consultantData
                        ->Consultant()->Status()->PhoneVerified()->Approval()->withCount(['consultation as consultationCount'])->orderByDesc('consultationCount')->take($param);
                }

            }
        }

        $totalConsultant['totalConsultantCount'] = $totalConsultantDataCount;
        $totalActiveConsulatnt['totalApprovedConsulatntCount'] = $totalApprovedConsulatntCount;
        $totalWaitingConsultant['totalWaitingConsultantCount'] = $totalWaitingConsultantCount;
        $totalRejectedConsultant['totalRejectedConsultantCount'] = $totalRejectedConsultantCount;

        $highestRatingConsultant['highestRatingConsultantCount'] = $highestRatingConsultantCount;
        $totalDeactivatedConsultant['totalDeactivatedConsultantCount'] = $totalDeactivatedConsultantCount;
        $highestPaidConsultant['highestPaidConsultantCount'] = $highestPaidConsultantCount;
        $highestConsultationConsultant['highestConsultationConsultantCount'] = $highestConsultationConsultantCount;
        $Cards = [$totalConsultant, $totalActiveConsulatnt, $totalWaitingConsultant, $totalRejectedConsultant,
            $highestRatingConsultant, $totalDeactivatedConsultant, $highestPaidConsultant, $highestConsultationConsultant];

        $ItemAll = [];

        if (isset($params['limit'])) {
            if (isset($params['offset'])) {
                $ItemAll['totalConsultant'] = $totalConsultantDataCount;
                $ItemAll['offset'] = $params['offset'];
                $ItemAll['limit'] = $params['limit'];
                $ItemAll['consultation'] = $consultantData->offset($params['offset'])->limit($params['limit'])->get();
            } else {
                $ItemAll['limit'] = $params['limit'];
                $ItemAll['consultation'] = $consultantData->limit($params['limit'])->get();
            }
        } else {
            $ItemAll['totalConsultant'] = $totalConsultantDataCount;
            $ItemAll['consultation'] = $consultantData->get();
        }
        $data['cardInformation'] = $Cards;
        $data['filterInformation'] = [$ItemAll];

        $message = "Successfully Data Shown";
        return $this->responseSuccess(200, true, $message, $data);
    }

    public function transferableConsultantList(Request $request)
    {
        // $params = $request->all();
        // foreach ($params as $key => $param) {

        // }

        $consultantData = User::select('id', 'name')->Consultant()->Status()->Approval()->withCount('initialConsultation', 'inprogressConsultation')->get();

        //     $caseIds = $request->input('case_id');

        //     if (!is_array($caseIds)) {
        //         $caseIds = [$caseIds];
        //     }

        //     $services = Service::whereHas('lcsCases', function ($query) use ($caseIds) {
        //         $query->whereIn('id', $caseIds);
        //     })->get();

        //     // return $services;
        //     // $serviceIds = [];

        //     // foreach ($services as $service) {
        //     //     $serviceIds[] = $service->id;
        //     // }

        //    $consultant = $consultantData->whereHas('services', function ($q) use ($services) {
        //     $q->whereIn('services.id', $services);
        //    });

        //     $consultantData = $consultant->get();
        //     // return $consultantData;

        //     //   return $consultant;
        //     //   if ($request->has('case_id') && $request->filled('case_id')) {
        //     //     $ids = explode(',', $request->services);
        //     //   return $ids;
        //     // }

        //     // $consultant = $consultant->whereHas('services', function ($q) use ($serviceId) {
        //     //     $q->where('services.id', $serviceId);
        //     // });

        $message = "Citizen Data Succesfully Shown";
        return $this->responseSuccess(200, true, $message, $consultantData);
    }

    public function changeConsultant()
    {

        $consultants_selected_fields = ['id', 'name', 'phone', 'email', 'address', 'code', 'type', 'profile_image', 'district_id', 'gender', 'rates', 'totalRating', 'active_status', 'years_of_experience', 'schedule'];

        $consultantData = User::select($consultants_selected_fields)
            ->Consultant()->Status()->Approval()
            ->withCount('initialConsultation', 'newMessageCount')
            ->get();

        // $consultantData = $consultantData->filter(function ($item) {
        //     if ($item->initial_consultation_count > 0 || $item->new_message_count_count > 0) {
        //         return $item;
        //     }
        // });

        $message = "Consultant Data Succesfully Shown";
        return $this->responseSuccess(200, true, $message, $consultantData);
    }

    // public function consultantCaseCardInformation(Request $request)
    // {
    //     $data = [];
    //     $id = auth()->user()->id;
    //     // Count all waiting Consultation->status(0)
    //     $data['waitingConsultation'] = LcsCase::Initial()
    //         ->where('consultant_id', $id)->count();

    //     // Count all running Consultation->status(1)
    //     $data['runningConsultation'] = LcsCase::InProgress()
    //         ->where('consultant_id', $id)->count();
    //     // Count all complete Consultation->status(2)
    //     $data['completeConsultation'] = LcsCase::Completed()
    //         ->where('consultant_id', $id)->count();

    //     // Count all cancel Consultation->status(3)
    //     $data['cancelConsultation'] = LcsCase::Cancel()
    //         ->where('consultant_id', $id)->count();

    //     $message = "Successfully Data Shown";
    //     return $this->responseSuccess(200, true, $message, $data);
    // }
    // public function consultantCaseList(Request $request)
    // {
    //     $type = auth()->user()->type;
    //     $userType = $type === 'citizen' ? 'consultant' : 'citizen';
    //     $caseData = DB::table('lcs_cases')
    //         ->where('lcs_cases.' . $type . '_id', auth()->user()->id)
    //         ->where(['deleted_at' => null])
    //         ->select(
    //             'lcs_cases.id as case_id',
    //             'lcs_cases.consultant_id',
    //             'lcs_cases.title',
    //             'lcs_cases.document_file',
    //             'lcs_cases.rating',
    //             'lcs_cases.document_link',
    //             'lcs_cases.case_initial_date',
    //             'lcs_cases.case_status_date',
    //             'lcs_cases.description',
    //             'lcs_cases.case_code',
    //             'lcs_cases.status',
    //             'lcs_cases.created_at',
    //             'lcs_cases.updated_at',
    //             'users.name',
    //             'users.code',
    //             'users.profile_image',
    //             'services.id as service_id',
    //             'services.title as service_title'
    //         )->join('users', 'lcs_cases.' . $userType . '_id', '=', 'users.id')
    //         ->join('services', 'lcs_cases.service_id', '=', 'services.id')
    //         ->orderBy('updated_at', 'DESC');

    //     if ($caseData->exists()) {
    //         // all waiting Consultation->status(0)
    //         if ($request->has('initial')) {
    //             $caseData = $caseData->where('lcs_cases.status', 0);
    //         }
    //         // all running Consultation->status(1)
    //         if ($request->has('running')) {
    //             $caseData = $caseData->where('lcs_cases.status', 1);
    //         }
    //         // all complete Consultation->status(2)
    //         if ($request->has('complete')) {
    //             $caseData = $caseData->where('lcs_cases.status', 2);
    //         }
    //         // all cancel Consultation->status(3)
    //         if ($request->has('rejected')) {
    //             $caseData = $caseData->where('lcs_cases.status', 3);
    //         }

    //         // service wise filter
    //         if ($request->has('service')) {
    //             $serviceId = $request->input('service');
    //             $caseData = $caseData->where('service_id', $serviceId);
    //         }

    //         // date wise filter
    //         if ($request->has('dateToDate')) {
    //             $dates = explode(' to ', str_replace('/', '-', $request->input('dateToDate')));
    //             // $startDate = Carbon::parse(trim($dates[0]))
    //             //     ->toDateTimeString();
    //             // $endDate = Carbon::parse(trim($dates[1]))
    //             //     ->toDateTimeString();
    //             $startDate = trim($dates[0]) . ' 00:00:00';
    //             $endDate = trim($dates[1]) . ' 23:59:59';

    //             $caseData = $caseData->whereBetween('created_at', [$startDate, $endDate]);

    //         }

    //         // rating wise filter
    //         if ($request->has('rating')) {
    //             $rating = $request->input('rating');
    //             if ($rating) {
    //                 $caseData = $caseData->where('lcs_cases.rating', $rating);
    //             }
    //         }
    //         // }

    //         $limit = $request->limit;
    //         $allCaseData = $caseData->paginate($limit ?? 20);

    //         if ($allCaseData) {
    //             $message = "Case list data succesfully shown";
    //             return $this->responseSuccess(200, true, $message, $allCaseData);
    //         }
    //     } else {
    //         $message = "No Data Found";
    //         return $this->responseError(404, false, $message);
    //     }
    // }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
