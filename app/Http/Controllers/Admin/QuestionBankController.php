<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Helper\SendNotification;
use App\Models\QuestionBank;
use App\Models\User;
use App\Traits\ResponseTrait;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuestionBankController extends Controller
{

    use ResponseTrait;

    public function questionBankCardInformation()
    {
        $data['allQuestion'] = QuestionBank::count();
        $data['pendingQuestion'] = QuestionBank::where('status', 0)
            ->count();
        $data['completeQuestion'] = QuestionBank::where('status', 1)
            ->count();

        if (!empty($data)) {
            $message = "Succesfully Data Shown";
            return $this->responseSuccess(200, true, $message, $data);
        }
    }

    public function questionBank($questionBnakId)
    {
        $questionBankData = QuestionBank::where('id', $questionBnakId)
            ->with('service:id,title', 'createdBy:id,name,profile_image', 'answeredBy:id,name,profile_image', 'updatedBy:id,name,profile_image')
            ->get();

        if (!empty($questionBankData)) {
            $message = "Succesfully Data Shown";
            return $this->responseSuccess(200, true, $message, $questionBankData);
        } else {
            $message = "Invalid credentials";
            return $this->responseError(403, false, $message);
        }
    }

    public function questionBankAll(Request $request)
    {
    //   $tableData=  DB::table('question_banks')

    //     ->update(['consultant_id' => 3020]);

        $questionBankData = QuestionBank::select('id', 'question', 'status', 'created_at', 'answer', 'question_code', 'service_id','consultant_id')
            ->with('service:id,title','remoteConsultant:id,name')
            ->latest();

        if ($questionBankData->exists()) {
            if ($request->has('all')) {
                $questionBankData = $questionBankData;
            }
            if ($request->has('pending')) {
                $questionBankData = $questionBankData->where('status', 0);
            }
            if ($request->has('compelete')) {
                $questionBankData = $questionBankData->where('status', 1);
            }
        }
        $params = $request->all();
        $limit = $request->limit;

        $questionBankData = $questionBankData->paginate($limit ?? 20);

        if (!empty($questionBankData)) {
            $message = "Succesfully Data Shown";
            return $this->responseSuccess(200, true, $message, $questionBankData);
        } else {
            $message = "Invalid credentials";
            return $this->responseError(403, false, $message);
        }
    }

    public function questionBankAllMobile(Request $request)
    {
        $data = [];
        $params = $request->all();

        $questionBankData = QuestionBank::with('service:id,title', 'createdBy:id,name,profile_image', 'answeredBy:id,name,profile_image', 'updatedBy:id,name,profile_image')
            ->latest();

        $questionBankDataCount = QuestionBank::count();

        if ($questionBankData->exists()) {
            if ($request->has('all')) {
                $questionBankDataCount = $questionBankDataCount;
                $questionBankData = $questionBankData;
            }
            if ($request->has('pending')) {
                $questionBankDataCount = $questionBankData->where('status', 0)->count();
                $questionBankData = $questionBankData->where('status', 0);
            }
            if ($request->has('compelete')) {
                $questionBankDataCount = $questionBankData->where('status', 1)->count();
                $questionBankData = $questionBankData->where('status', 1);
            }
        }

        if (isset($params['limit'])) {
            if (isset($params['offset'])) {
                $data['offset'] = $params['offset'];
                $data['limit'] = $params['limit'];
                $data['totalQuestionBank'] = $questionBankDataCount;
                $data['list'] = $questionBankData->offset($params['offset'])
                    ->limit($params['limit'])
                    ->get();
            } else {
                $data['totalQuestionBank'] = $questionBankDataCount;
                $data['limit'] = $params['limit'];
                $data['list'] = $questionBankData->limit($params['limit'])->get();
            }
        } else {
            $data['totalQuestionBank'] = $questionBankDataCount;
            $data['list'] = $questionBankData->get();
        }

        // $questionBankData = $questionBankData->paginate($limit ?? 20);

        if (!empty($questionBankData)) {
            $message = "Succesfully Data Shown";
            return $this->responseSuccess(200, true, $message, $data);
        } else {
            $message = "Invalid credentials";
            return $this->responseError(403, false, $message);
        }
    }

    public function questionBankStore(Request $request)
    {

        $questionCode = mt_rand(10000000, 99999999);

        // DB::beginTransaction();
        try {

            $request->validate([
                'service_id' => 'required',
                'question' => 'required|string',
                'question_details' => 'nullable|string',
                'answer' => 'nullable',
                'description' => 'nullable',
                'consultant_id' => 'nullable|required|string',
            ]);

            $data = QuestionBank::create([
                'service_id' => $request->service_id,
                'question' => $request->question,
                'question_details' => $request->question_details,
                'status' => 0,
                'created_by' => auth()->id(),
                'question_code' => $questionCode,
                'case_codes' => $request->case_codes ?? null,
                'case_ids' => $request->case_ids ?? null,
                'consultant_id' => $request->consultant_id,
            ]);

            // $deviceToken = User::where('type', 'remoteconsultant')->whereNotNull('device_token')->pluck('device_token')->all();

            $deviceToken = ['device_token' => User::where('type', 'remoteconsultant')->where('id', $request->consultant_id)->first()->device_token];

            // Create a new instance of the SendNotification class
            $sendNotification = new SendNotification();

            // Check if device token and userData and approvalStatus are present in the request
            if ($deviceToken != null && !empty($deviceToken) && !empty($request->question)) {
                // Set the FCM token, title, and body
                // $FcmToken = $deviceToken;
                $FcmToken = [$deviceToken['device_token']];
                $title = 'সন্মানিত ভূমিসেবা পরামর্শক';
                $body = 'আপনার কাছে একটি নতুন প্রশ্ন এসেছে, অনুগ্রহপূর্বক উত্তর প্রদান করুন ।';
                $message = "Question Bank Created Successfull";

                if ($this->responseSuccess(200, true, $message, $data)) {
                    $sendNotification->sendNotification($FcmToken, $title, $body);
                }
                // if ($this->responseSuccess(200, true,$message, $data)) {
                //     dispatch(new SendPushNotificationJob($FcmToken, $title, $body));
                // }
            }

            // DB::commit();
            $message = "Question Bank Created Successfull";
            return $this->responseSuccess(200, true, $message, $data);
        } catch (QueryException $e) {
            // DB::rollBack();
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, $e->getMessage());
        }
    }

    public function questionBankUpdate(Request $request, $id)
    {
        DB::beginTransaction();
        $questionBankData = QuestionBank::findOrFail($id);

        if (isset($request->answer)) {
            $status = 1;
            $answerBy = auth()->id();
        }

        try {
            if ($questionBankData) {
                $questionBankData->update([
                    'answer' => $request->answer ?? $questionBankData->answer,
                    'answered_by' => $answerBy ?? $questionBankData->answered_by,
                    'updated_by' => auth()->id(),
                    'service_id' => $request->service_id ?? $questionBankData->service_id,
                    'question' => $request->question ?? $questionBankData->question,
                    'question_details' => $request->question_details ?? $questionBankData->question_details,
                    'created_by' => $questionBankData->created_by,
                    'case_codes' => $request->case_codes ?? $questionBankData->case_codes,
                    'case_ids' => $request->case_ids ?? $questionBankData->case_ids,
                    'status' => $status ?? $questionBankData->status,
                ]);

                $message = "This question bank data has been updated";
                DB::commit();
                return $this->responseSuccess(200, true, $message, $questionBankData);
            }
        } catch (QueryException $e) {
            DB::rollBack();
        }
    }

    public function adminFaqReport(Request $request)
    {

        $questionBankData = DB::table('question_banks')
            ->select(
                'question_banks.id',
                'question_banks.service_id',
                'question_banks.question',
                'question_banks.status',
                'question_banks.question_code',
                'question_banks.created_at',
                'question_banks.updated_at',
                'createdBy.name as createdByName',
                'answeredBy.name as answeredByName',
                'updatedBy.name as updatedByName',
                'services.title as service_title',
            )
            ->join('users as createdBy', 'question_banks.created_by', '=', 'createdBy.id')
            ->join('users as answeredBy', 'question_banks.answered_by', '=', 'answeredBy.id')
            ->join('users as updatedBy', 'question_banks.updated_by', '=', 'updatedBy.id')
            ->join('services', 'question_banks.service_id', '=', 'services.id')
            ->orderByDesc('id');
        // return $CaseData;
        // $CaseData = LcsCase::with(['citizen:id,name,phone', 'consultant:id,name,phone', 'service:id,title'])
        //     ->select('id', 'status', 'citizen_id', 'consultant_id',
        //         'service_id', 'case_code', 'case_initial_date', 'rating', 'created_at','deleted_at')
        //     ->where(['deleted_at' => null])
        //     ->latest();

        // all waiting Consultation->status(0)
        if ($request->has('pending')) {
            $questionBankData = $questionBankData->where('question_banks.status', 0);
        }
        // all running Consultation->status(1)
        if ($request->has('complete')) {
            $questionBankData = $questionBankData->where('question_banks.status', 1);
        }

        // service wise filter
        if ($request->has('service')) {
            $serviceId = $request->input('service');
            $questionBankData = $questionBankData->where('question_banks.service_id', $serviceId);
        }

        // date wise filter
        if ($request->has('dateToDate')) {
            $dates = explode(' to ', str_replace('/', '-', $request->input('dateToDate')));
            // $startDate = Carbon::parse(trim($dates[0]))
            //     ->toDateTimeString();

            // $endDate = Carbon::parse(trim($dates[1]))
            //     ->toDateTimeString();
            $startDate = trim($dates[0]) . ' 00:00:00';
            $endDate = trim($dates[1]) . ' 23:59:59';

            $questionBankData = $questionBankData->whereBetween('question_banks.created_at', [$startDate, $endDate]);

        }

        // rating wise filter
        // if ($request->has('rating')) {
        //     $rating = $request->input('rating');
        //     if ($rating) {
        //         $CaseData = $CaseData->where('question_banks.rating', $rating);
        //     }
        // }

        $limit = $request->limit;
        $data = $questionBankData->paginate($limit ?? 20);

        if (!empty($data)) {
            $message = "Succesfully Data Shown";
            return $this->responseSuccess(200, true, $message, $data);
        } else {
            $message = "Invalid credentials";
            return $this->responseError(403, false, $message);
        }
    }

    // for remote consultant list
    public function remoteConsultantList()
    {
        $remoteConsultantList = User::select('id', 'name', 'phone', 'profile_image')->where('type', 'remoteconsultant')
            ->get();

        if ($remoteConsultantList->isEmpty()) {
            $message = "No Data Found";
            return $this->responseError(404, false, $message);
        }

        $message = "Succesfully Data Shown";
        return $this->responseSuccess(200, true, $message, $remoteConsultantList);
    }
}
