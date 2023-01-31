<?php

namespace App\Http\Controllers\Citizen;

use App\Models\User;
use App\Models\LcsCase;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use Illuminate\Http\Response;
use App\Http\Requests\CaseRequest;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;

class CaseController extends Controller
{
    use ResponseTrait;
    public function store(CaseRequest $request)
    {
        DB::beginTransaction();
        try {
            $case = LcsCase::where('citizen_id', auth()->user()->id)
                ->where('consultant_id', $request->consultant_id)
                ->latest()->first();
            if ($case) {
                $caseCode = $case->case_code;
                $output = substr($caseCode, 0, strrpos($caseCode, '-'));
                $codeNumber = explode("-", $caseCode)[4] + 1;
                $finalCode = $output . '-' . $codeNumber;
                //return $finalCode;
             }
            else {
               // $citizenCodeNo = 'case-' . date('d-m-y-') . str_pad($citizenData, 4, '0', STR_PAD_LEFT);
                $citizenInfo = User::where('id',  auth()->user()->id)->first();
                $citizenCode = $citizenInfo->code;
                $citizenLastCodeNumber = explode("-", $citizenCode)[4]; //0002

                $consultantInfo = User::where('id', $request->consultant_id)->first();
                $consultantCode = $consultantInfo->code;
                $consultantLastCodeNumber = explode("-", $consultantCode)[4]; //0002
               // return $consultantLastCodeNumber;
              // $codeFinalNumber2 = 'case-' . date('dmy-') . str_pad($citizenData, 4, '0', STR_PAD_LEFT);
              $codeFinalNumber2 = 'case'. '-' . date('dmy') . '-' .$citizenLastCodeNumber. '-' .$consultantLastCodeNumber. '-' .uniqid() ;
                return $codeFinalNumber2;
            }





            // $post->visitors = $post->visitors - 1;
            //  return ;
            // $Case = User::select(DB::raw('count(id) as total'))
            //     ->where('type', 'citizen')
            //     ->first();
            // $citizenData = $Case->total + 1;
            // $citizenCodeNo = 'case-' . date('d-m-y-') . str_pad($citizenData, 4, '0', STR_PAD_LEFT);

            $data = LcsCase::create([
                'service_id' => $request->service_id,
                'citizen_id' => $request->citizen_id,
                'consultant_id' => $request->consultant_id,
                'title' => $request->title,
                'status' => $request->status,
                'file' => $request->file,
                'case_initial_date' => $request->case_initial_date,
                'case_status_date' => $request->case_status_date,
                'consultant_review_comment' => $request->consultant_review_comment,
                'citizen_review_comment' => $request->citizen_review_comment,
                'case_code' => $finalCode,
            ]);

            DB::commit();
            $message = "Case Created Successfull";
            return $this->responseSuccess(200, true, $message, $data);

        } catch (QueryException $e) {
            DB::rollBack();
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, $e->getMessage());
        }
    }
    // $service = DB::table('services')->get();
    public function ConsultantServices($id)
    {
        DB::beginTransaction();
        try {
            $services = User::with('services:id,title')->where('id', $id)->active()->first()['services'];
            DB::commit();
            $message = "Consultant Services ShownSuccessfull";
            return $this->responseSuccess(200, true, $message, $services);
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, $e->getMessage());
        }
    }
}
