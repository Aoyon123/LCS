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
                $codeNumber = explode("-", $caseCode)[2] + 1;
                $finalCode = $output . '-' . $codeNumber;
                //return $finalCode;
            } else {
                // con-100123-0009
                $citizenInfo = User::where('id', auth()->user()->id)->first();
                //return $citizenInfo;
                $citizenCode = $citizenInfo->code;
                $citizenLastCodeNumber = explode("-", $citizenCode)[2];
                //  return $citizenLastCodeNumber;
                $consultantInfo = User::where('id', $request->consultant_id)->first();
                // return $request->consultant_id;
                //  return $consultantInfo;
                $consultantCode = $consultantInfo->code;
                $consultantLastCodeNumber = explode("-", $consultantCode)[2];

                $codeTotalData = LcsCase::select(DB::raw('count(id) as total'))
                    ->where('id', auth()->user()->id)
                    ->first();
                $citizenData = $codeTotalData->total + 1;

                $codeFinalNumber = 'case' . '-' . date('dmy') . '-' . $citizenLastCodeNumber . '-' . $consultantLastCodeNumber . '-' . $citizenData;
            }


            if ($request->file) {
                $file_parts = explode(";base64,", $request->file);
                if (isset($file_parts[1])) {
                    $case_file_path = FileHandler::uploadFile($request->file, $request->citizen_id, 'caseFile');
                    if (File::exists($case_file_path)) {
                        File::delete($case_file_path);
                    }
                }
                // else {
                //     $case_file_path = $user->file;
                // }
            }


            // if ($request->hasFile('file')) {
            //     $request->validate([
            //         'file' => 'mimes:csv,txt,xlx,xls,pdf',
            //       //  'file.*' => 'mimes:csv,txt,xlx,xls,pdf'
            //     ]);
            //     $file_parts = explode(";base64,", $request->file);
            //     $filename_path = md5(time() . uniqid()) . ".pdf";
            //      if (isset($file_parts[1])) {
            //         $decoded = base64_decode($file_parts[1]);
            //         file_put_contents(public_path() . "/uploads/caseFile/" . $filename_path, $decoded);
            //         $case_file = "/uploads/caseFile/" . $filename_path;
            //         if (File::exists($case_file)) {
            //             File::delete($case_file);
            //         }
            //      }
            // }

            // $multipleFiles = [];
            // foreach ($request->file('file') as $files) {
            //     $fileName = 'case' . '-' . $request->citizen_id . '.' . $files->getClientOriginalExtension();
            //     $destinationPath = public_path('uploads/caseFile/');
            //     $files->move($destinationPath, $fileName);
            //     // $case_file_path_save = '/uploads/caseFile/' . $fileName;
            //     $multipleFiles[] = $fileName;
            // }
            // // return $multipleFiles;
            // $files = new LcsCase();
            // $files->file = $multipleFiles;
            //  $file->save();


            // $file = $request->file('file');
            // $fileName = 'case' . '-' . $request->citizen_id . '.' . $file->getClientOriginalExtension();
            // $destinationPath = public_path('uploads/caseFile/');
            // $file->move($destinationPath, $fileName);
            // $case_file_path_save = '/uploads/caseFile/' . $fileName;
            // if ($request->nid_front) {
            //     $image_parts = explode(";base64,", $request->nid_front);
            //     if (isset($image_parts[1])) {
            //         $nid_front_image_path = FileHandler::uploadImage($request->nid_front, $request->type, $request->email, 'nid_front');
            //         if (File::exists($nid_front_image_path)) {
            //             File::delete($nid_front_image_path);
            //         }
            //     } else {
            //         $nid_front_image_path = $user->nid_front;
            //     }
            // }

            // if ($request->file) {
            //     $file_parts = explode(";base64,", $request->file);
            //     $filename_path = md5(time() . uniqid()) . ".pdf";
            //      if (isset($file_parts[1])) {
            //         $decoded = base64_decode($file_parts[1]);
            //         file_put_contents(public_path() . "/uploads/caseFile/" . $filename_path, $decoded);
            //         $case_file = "/uploads/caseFile/" . $filename_path;
            //         if (File::exists($case_file)) {
            //             File::delete($case_file);
            //         }
            //      }
            //     //else {
            //     //     $certification_copy = $academic->certification_copy;
            //     // }
            //        // return $case_file;
            // }

            // if ($request->file) {
            //    // $image_parts = explode(";base64,", $request->file);
            //     $filename_path = 'case' . '-' . $request->citizen_id  . ".pdf";
            //     // if (isset($image_parts[1])) {
            //         $decoded = base64_decode($request->file);
            //         file_put_contents(public_path() . "/uploads/caseFile/" . $filename_path, $decoded);
            //         $case_file = "/uploads/caseFile/" . $filename_path;
            //         if (File::exists($case_file)) {
            //             File::delete($case_file);
            //         }
            // } else {
            //     $file = $academic->certification_copy;
            // }
            // return $case_file;
            // }

            //multiple file upload
            // $this->validate($request, [
            //     'filenames' => 'required',
            //     'filenames.*' => 'required'
            // ]);

            // $files = [];
            // if ($request->hasfile('filenames')) {
            //     foreach ($request->file('filenames') as $file) {
            //         $name = time() . rand(1, 100) . '.' . $file->extension();
            //         $file->move(public_path('files'), $name);
            //         $files[] = $name;
            //     }
            // }

            // $file = new File();
            // $file->filenames = $files;
            // $file->save();

            $data = LcsCase::create([
                'service_id' => $request->service_id,
                'citizen_id' => auth()->user()->id,
                'consultant_id' => $request->consultant_id,
                'title' => $request->title,
                'status' => 0,
                'link' => $request->link,
                'rating' => $request->rating,
                'description' => $request->description,
                'case_initial_date' => Carbon::now()->toDateString(),
                // 'case_status_date' => $request->case_status_date,
                // 'consultant_review_comment' => $request->consultant_review_comment,
                // 'citizen_review_comment' => $request->citizen_review_comment,
                'case_code' => $finalCode ?? $codeFinalNumber,
                 'file' => $case_file_path,
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
