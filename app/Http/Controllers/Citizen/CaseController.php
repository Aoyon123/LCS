<?php

namespace App\Http\Controllers\Citizen;

use App\Models\User;
use App\Models\LcsCase;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use Illuminate\Http\Response;
use App\Http\Helper\SMSHelper;
use Illuminate\Support\Carbon;
use App\Http\Helper\FileHandler;
use App\Http\Requests\CaseRequest;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;

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

    public function caseList()
    {
        $type = auth()->user()->type;
        //  return $type;
        $userType = $type === 'citizen' ? 'consultant' : 'citizen';
        $caseData = DB::table('lcs_cases')
            ->where('lcs_cases.' . $type . '_id', auth()->user()->id)
            ->where(['deleted_at' => null])
        //    ->where('lcs_cases.status', '2')
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
                'users.name',
                'users.code',
                'users.profile_image',
                'services.id as service_id',
                'services.title as service_title'
            )->join('users', 'lcs_cases.' . $userType . '_id', '=', 'users.id')
            ->join('services', 'lcs_cases.service_id', '=', 'services.id')
            ->orderBy('case_id', 'DESC')->get();
        // return $caseData;

        if ($caseData) {
            $message = "Case list data succesfully shown";
            return $this->responseSuccess(200, true, $message, $caseData);
        } else {
            $message = "No Data Found";
            return $this->responseError(404, false, $message);
        }
    }

    public function adminCaseList($type, $user_id)
    {
        //$type = $request->type;
        //  return $type;
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
        DB::beginTransaction();
        try {

            $consultantData = User::where(['id' => $request->consultant_id])->first();
            // return $consultantData->phone;

            $case = LcsCase::where('citizen_id', auth()->user()->id)
                ->where('consultant_id', $request->consultant_id)
                ->latest()->first();

            $case_file_path = null;

            $now = Carbon::now();
            $case_code = $now->format('Hsu');

            // if ($case) {
            //     $caseCode = $case->case_code;
            //     $output = substr($caseCode, 0, strrpos($caseCode, '-'));
            //     // return $output;
            //     $codeNumber = explode("-", $caseCode)[4] + 1;
            //     $case_code = $output . '-' . $codeNumber;
            // } else {

            //     $citizenInfo = User::where('id', auth()->user()->id)->first();

            //     $citizenCode = $citizenInfo->code;

            //     $citizenLastCodeNumber = explode("-", $citizenCode)[2];

            //     $consultantInfo = User::where('id', $request->consultant_id)->first();

            //     $consultantCode = $consultantInfo->code;

            //     $consultantLastCodeNumber = explode("-", $consultantCode)[2];

            //     $codeTotalData = LcsCase::select(DB::raw('count(id) as total'))
            //         ->where('id', auth()->user()->id)
            //         ->first();

            //     $citizenData = $codeTotalData->total + 1;

            //     $case_code = 'case' . '-' . date('dmy') . '-' . $citizenLastCodeNumber . '-' . $consultantLastCodeNumber . '-' . $citizenData;
            // }

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
            ]);

            $smsMessage = 'Dear Consultant A Citizen Sent You A New Service Request';
            $messageSuccess = SMSHelper::sendSMS($consultantData->phone, $smsMessage);


            DB::commit();
            $message = "Consultation Created Successfull";
            return $this->responseSuccess(200, true, $message, $data);
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, $e->getMessage());
        }
    }

    public function update(CaseRequest $request)
    {
        DB::beginTransaction();

        $caseData = LcsCase::findOrFail($request->id);
        // return $caseData;
        try {
            if ($caseData) {
                $caseData->update([
                    'status' => $request->status ?? $caseData->status,
                    'consultant_review_comment' => $request->consultant_review_comment ?? $caseData->consultant_review_comment,
                    'case_status_date' => Carbon::now()->toDateTimeString(),
                    'rating' => $request->rating ?? $caseData->rating,
                    'citizen_review_comment' => $request->citizen_review_comment ?? $caseData->citizen_review_comment,
                ]);


                if ($request->rating) {
                    $consultant_id = $caseData->consultant_id;
                    //  return $consultant_id;
                    $averageRating = LcsCase::where(['consultant_id' => $consultant_id, 'status' => 2])
                        ->avg('rating');

                    $totalRating = LcsCase::where([
                        'consultant_id' => $consultant_id,
                        'status' => 2,
                    ])->whereNotNull('rating')->count('rating');

                    // if(!empty($totalRating)){
                    //     $fixedCount = 1;
                    // }
                // return $totalRating;
                    $roundRating = round($averageRating, 1);
                    User::find($consultant_id)->update(['rates' => $roundRating, 'totalRating' => $totalRating ?? 1]);
                }

                $message = "This Consultation data has been updated";
                DB::commit();
                return $this->responseSuccess(200, true, $message, $caseData);
            }
        } catch (QueryException $e) {
            DB::rollBack();
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

        if (Auth::check() && Auth::user()->type == 'admin' || $caseData->citizen_id == Auth::user()->id || $caseData->consultant_id == Auth::user()->id){
            $message = "Consultation details data succesfully shown";
            return $this->responseSuccess(200, true, $message, $caseData);
        }

         else {
            $message = "No Data Found";
            return $this->responseError(404, false, $message);
        }
    }

    public function initialCaseList()
    {
         $data = LcsCase::where('status', 0)
            ->whereDate('updated_at', '<=', date('Y-m-d H:i:s', strtotime('-3 days')))
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


        if($data){
            $caseData = LcsCase::where('id', $request->consultation_id)->first();;
            if($caseData){
            $caseData->update([
                'consultant_id' => $request->consultant_id
            ]);
        }
        }

       // $smsMessage = 'Dear Consultant A New Service Request Admin Forwaded to You';
        $messageSuccess = SMSHelper::sendSMS($consultantData->phone, $request->message);

        if (!empty($caseData)) {
            $message = "This Consultation Transfer successfully";
            return $this->responseSuccess(200, true, $message, $caseData);
        } else {
            $message = "Invalid credentials";
            return $this->responseError(403, false, $message);
        }
    }
}
