<?php

namespace App\Http\Controllers\Citizen;

use App\Models\User;
use App\Models\LcsCase;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use App\Http\Helper\FileHandler;
use App\Http\Requests\CaseRequest;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Database\QueryException;

class CaseController extends Controller
{
    use ResponseTrait;

    public function index()
    {
        $data = LcsCase::all();
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
        // $type = auth()->user()->type;
        // $cases = [];
        // $case_selected_fields = ['id', 'title', 'document_file', 'document_link', 'case_code'];
        // if ($type == 'citizen') {
        //     $cases = LcsCase::with(['consultant:id,name,code,profile_image'])->select($case_selected_fields)->get();

        // } elseif ($type == 'consultant') {
        //     $cases = LcsCase::with(['citizen:id,name,code,profile_image'])->select($case_selected_fields)->get();
        // }
        // return $cases;
        $type = auth()->user()->type;
        //  return $type;
        $userType = $type === 'citizen' ? 'consultant' : 'citizen';
        $caseData = DB::table('lcs_cases')
            ->where('lcs_cases.' . $type . '_id', auth()->user()->id)
            ->where('deleted_at', null)
            ->select(
                'lcs_cases.id as case_id',
                'lcs_cases.title',
                'lcs_cases.document_file',
                'lcs_cases.document_link',
                'lcs_cases.case_initial_date',
                'lcs_cases.case_status_date',
                'lcs_cases.description',
                'lcs_cases.case_code',
                'lcs_cases.status',
                'users.name',
                'users.code',
                'users.profile_image'
            )->join('users', 'lcs_cases.' . $userType . '_id', '=', 'users.id')->orderBy('case_id','DESC')->get();

        if ($caseData) {
            $message = "Case list data succesfully shown";
            return $this->responseSuccess(200, true, $message, $caseData);
        } else {
            $message = "No Data Found";
            return $this->responseError(404, false, $message);
        }
    }


    public function store(CaseRequest $request)
    {
        DB::beginTransaction();
        try {
            $case = LcsCase::where('citizen_id', auth()->user()->id)
                ->where('consultant_id', $request->consultant_id)
                ->latest()->first();
            $case_code = '';
            $case_file_path = null;

            if ($case) {
                $caseCode = $case->case_code;
                $output = substr($caseCode, 0, strrpos($caseCode, '-'));
                // return $output;
                $codeNumber = explode("-", $caseCode)[4] + 1;
                $case_code = $output . '-' . $codeNumber;
            } else {

                $citizenInfo = User::where('id', auth()->user()->id)->first();

                $citizenCode = $citizenInfo->code;
                //  return $citizenCode;

                $citizenLastCodeNumber = explode("-", $citizenCode)[2];

                $consultantInfo = User::where('id', $request->consultant_id)->first();

                $consultantCode = $consultantInfo->code;
                $consultantLastCodeNumber = explode("-", $consultantCode)[2];

                $codeTotalData = LcsCase::select(DB::raw('count(id) as total'))
                    ->where('id', auth()->user()->id)
                    ->first();

                $citizenData = $codeTotalData->total + 1;

                $case_code = 'case' . '-' . date('dmy') . '-' . $citizenLastCodeNumber . '-' . $consultantLastCodeNumber . '-' . $citizenData;
            }

            if ($request->document_file) {
                $extension = '';
                $file_parts = explode(";base64,", $request->document_file);
                $extension_part = $file_parts[0];
                // return $extension_part;
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

            $data = LcsCase::create([
                'service_id' => $request->service_id,
                'citizen_id' => auth()->user()->id,
                'consultant_id' => $request->consultant_id,
                'title' => $request->title,
                'status' => 0,
                'document_link' => $request->document_link,
                'rating' => $request->rating,
                'description' => $request->description,
                'case_initial_date' => Carbon::now()->toDateTimeString(),
                'case_status_date' => $request->case_status_date,
                'consultant_review_comment' => $request->consultant_review_comment,
                'citizen_review_comment' => $request->citizen_review_comment,
                'case_code' => $case_code,
                'document_file' => $case_file_path,
            ]);

            DB::commit();
            $message = "Case Created Successfull";
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
                    $averageRating = LcsCase::where(['consultant_id' => $consultant_id, 'status' => 2])->avg('rating');
                    $roundRating = round($averageRating, 1);

                    User::find($consultant_id)->update(['rates' => $roundRating]);
                }

                $message = "This " . $caseData->case_code . " data has been updated.";
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

            $message = "Case Deleted Succesfully";
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
            // $services = [];
            // $user = User::with('services:id,title')->where('id', $id)->first();
            // if ($user) {
            //     $services = $user['services'];
            // }
            // return $user;
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

        if ($caseData) {
            $message = "Case details data succesfully shown";
            return $this->responseSuccess(200, true, $message, $caseData);
        } else {
            $message = "No Data Found";
            return $this->responseError(404, false, $message);
        }
    }
}
