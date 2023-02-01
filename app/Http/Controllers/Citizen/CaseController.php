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
use Symfony\Component\Mime\Part\File;
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

    public function store(CaseRequest $request)
    {
        //  return $request->all();
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
            } else {

                $citizenInfo = User::where('id', auth()->user()->id)->first();
                $citizenCode = $citizenInfo->code;
                $citizenLastCodeNumber = explode("-", $citizenCode)[4];

                $consultantInfo = User::where('id', $request->consultant_id)->first();
                $consultantCode = $consultantInfo->code;
                $consultantLastCodeNumber = explode("-", $consultantCode)[4];

                $codeTotalData = LcsCase::select(DB::raw('count(id) as total'))
                    ->where('id', auth()->user()->id)
                    ->first();
                $citizenData = $codeTotalData->total + 1;

                $codeFinalNumber2 = 'case' . '-' . date('dmy') . '-' . $citizenLastCodeNumber . '-' . $consultantLastCodeNumber . '-' . $citizenData;
            }


            if ($request->hasFile('file')) {
                $request->validate([
                    'file' => 'mimes:csv,txt,xlx,xls,pdf',
                ]);
            }
            $file = $request->file('file');
            $fileName = 'case' . '_' . $request->citizen_id . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('uploads/caseFile/');
            $file->move($destinationPath, $fileName);
            $case_file_path_save = '/uploads/caseFile/' . $fileName;

            $data = LcsCase::create([
                'service_id' => $request->service_id,
                'citizen_id' => $request->citizen_id,
                'consultant_id' => $request->consultant_id,
                'title' => $request->title,
                'status' => $request->status,
                'case_initial_date' => $request->case_initial_date,
                'case_status_date' => $request->case_status_date,
                'consultant_review_comment' => $request->consultant_review_comment,
                'citizen_review_comment' => $request->citizen_review_comment,
                'case_code' => $finalCode ?? $codeFinalNumber2,
                'file' => $case_file_path_save,
            ]);

            DB::commit();
            $message = "Case Created Successfull";
            return $this->responseSuccess(200, true, $message, $data);

        } catch (QueryException $e) {
            DB::rollBack();
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, $e->getMessage());
        }
    }

    public function update(CaseRequest $request, $id)
    {
        $input = LcsCase::findOrFail($id);

        DB::beginTransaction();
        try {
            if ($request->hasFile('file')) {
                $request->validate([
                    'file' => 'mimes:csv,txt,xlx,xls,pdf',
                ]);
            }
            $file = $request->file('file');
            $fileName = 'case' . '_' . $request->citizen_id . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('uploads/caseFile/');
            $file->move($destinationPath, $fileName);
            $case_file_path_save = '/uploads/caseFile/' . $fileName;

            if ($input) {
                $input->service_id = $request['service_id'];
                $input->citizen_id = $request['citizen_id'];
                $input->consultant_id = $request['consultant_id'];
                $input->title = $request['title'];
                $input->status = $request['status'];
                $input->case_initial_date = $request['case_initial_date'];
                $input->case_status_date = $request['case_status_date'];
                $input->consultant_review_comment = $request['consultant_review_comment'];
                $input->citizen_review_comment = $request['citizen_review_comment'];
                $input->case_code = $request['case_code'];
                $input->file = $case_file_path_save;

                $input->save();
                $message = "Updated Succesfully";

                DB::commit();
                return $this->responseSuccess(200, true, $message, $input);
            } else {
                $message = "No Data Found";
                return $this->responseError(404, false, $message);
            }
        } catch (QueryException $e) {
            DB::rollBack();
        }
    }

    public function destroy(Request $request, $id)
    {

        DB::beginTransaction();
        try {
            $user = LcsCase::findOrFail($id);
            if ($user) {
                $user->delete();
                $message = "Case Deleted Succesfully";
                DB::commit();
                return $this->responseSuccess(200, true, $message, []);
            }
        } catch (QueryException $e) {
            DB::rollBack();
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
