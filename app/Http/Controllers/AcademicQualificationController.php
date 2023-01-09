<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AcademicQualification;
use App\Traits\ResponseTrait;
use Illuminate\Http\Response;
use Illuminate\Database\QueryException;
use App\Http\Requests\AcademicQualificationRequest;


class AcademicQualificationController extends Controller
{
    use ResponseTrait;
    public function index()
    {
        $data = AcademicQualification::all();
        if (!empty($data)) {
            $message = "Succesfully Data Shown";
            return $this->responseSuccess(200, true, $message, $data);
        } else {
            $message = "Invalid credentials";
            return $this->responseError(403, false, $message);
        }
    }
    public function store(AcademicQualificationRequest $request)
    {
        DB::beginTransaction();
        try {

            if ($request->hasFile('certification_copy')) {
                $request->validate([
                    'certification_copy' => 'max:2024|mimes:jpeg,jpg,png,gif',
                ]);
            }

            $image = $request->file('certification_copy');
            $imageName = 'certificate' . $request->user_id . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('uploads/certificate/');
            $image->move($destinationPath, $imageName);
            $certification_image_path_save = '/uploads/certificate/' . $imageName;


            $data = AcademicQualification::create([
                'education_level' => $request->education_level,
                'institute_name' => $request->institute_name,
                'passing_year' => $request->passing_year,
                'certification_copy' => $certification_image_path_save,
                'user_id' => $request->user_id,

            ]);

            DB::commit();
            $message = "Data Inserted Successfull";
            return $this->responseSuccess(200, true, $message, $data);

        } catch (QueryException $e) {
            DB::rollBack();
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, $e->getMessage());
        }
    }

    public function retrieve($id)
    {
        DB::beginTransaction();
        try {
            $data = AcademicQualification::findOrFail($id);
            $message = "Data Found";
            DB::commit();
            return $this->responseSuccess(200, true, $message, $data);
        } catch (QueryException $e) {
            DB::rollBack();
        }

    }
    public function update(AcademicQualificationRequest $request, $id)
    {

        $input = AcademicQualification::findOrFail($id);

        DB::beginTransaction();
        try {

            if ($request->hasFile('certification_copy')) {
                $request->validate([
                    'certification_copy' => 'max:2024|mimes:jpeg,jpg,png,gif',
                ]);
            }

            $image = $request->file('certification_copy');
            $imageName = 'certificate' . $request->user_id . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('uploads/certificate/');
            $image->move($destinationPath, $imageName);
            $certification_image_path_save = '/uploads/certificate/' . $imageName;

            if ($input) {
                $input->education_level = $request['education_level'];
                $input->institute_name = $request['institute_name'];
                $input->passing_year = $request['passing_year'];
                $input->certification_copy = $certification_image_path_save;

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


    // public function destroy($id)
    // {
    //     $user = EducationQualification::findOrFail($id);
    //     //return $user;
    //     try {
    //         if ($user) {
    //            // return $user;
    //             $user->delete();

    //             $message = "Deleted Succesfully";
    //             return $this->responseSuccess(200, true, $message, $user);
    //         } else {
    //             $message = "Data cannot be deleted";
    //             return $this->responseError(403, false, $message);
    //         }
    //     } catch (QueryException $e) {
    //         DB::rollBack();
    //     }
    // }

    public function destroy(Request $request)
    {
        DB::beginTransaction();
        try {

            $users = DB::table('academic_qualifications')->whereIn('id', $request->all());
            $users->delete();
            $message = "Deleted Succesfully";
            DB::commit();
            return $this->responseSuccess(200, true, $message, []);

        } catch (QueryException $e) {
            DB::rollBack();
        }
    }
}
