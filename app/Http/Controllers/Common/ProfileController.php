<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Http\Helper\FileHandler;
use App\Models\AcademicQualification;
use App\Models\Experience;
use App\Models\User;
use App\Traits\ResponseTrait;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;

class ProfileController extends Controller
{
    use ResponseTrait;
    public function update(Request $request)
    {

        // return $number;
        //  return $request->all();
        DB::beginTransaction();
        try {

            $user = User::findOrFail($request->id);
            // return $request->services;
            // return $user->services()->sync($request->services);
            if (!preg_match('#^(\d{2})/(\d{2})/(\d{4})$#', $request->dob, $matches)) {
                $message = "Date Format Not Valid";
                return $this->responseError(403, false, $message);
            }

            $dob = mktime(0, 0, 0, $matches[2], $matches[1], $matches[3]);
            // return $dob;

            if (date('d/m/Y', $dob) != $request->dob) {
                $message = "Date Format Not Valid";
                return $this->responseError(403, false, $message);
            }

            $request->validate([
                'name' => 'required|string|max:50',
                'phone' => 'max:15|min:11|regex:/(01)[0-9]{9}/|required|unique:users,phone,' . $user->id,
                'password' => 'nullable|min:8',
                'address' => 'required|max:50',
                // 'nid' => 'regex:/(?:\d{17}|\d{13}|\d{10})/|unique:users,nid,' . $user->id,
                //  'nid' => 'nullable|unique:users,nid,' . $user->id,
                'dob' => 'required|max:50',
                'gender' => 'required|max:10',
                'district_id' => 'required|max:50',
            ]);

            if ($request->profile_image) {
                $image_parts = explode(";base64,", $request->profile_image);
                $imageType = explode("/", $image_parts[0])[1];
                $unique = uniqid();
                if (isset($image_parts[1])) {
                    // return $request->profile_image;
                    $profile_image_path = FileHandler::uploadImage($request->profile_image, $user->type, $unique, $imageType, 'profile');
                    if (File::exists(public_path($user->profile_image))) {
                        File::delete(public_path($user->profile_image));
                    }
                } else {
                    $profile_image_path = $user->profile_image;
                }
            } else {
                $profile_image_path = $user->profile_image;
            }

            if (strtolower($request->type) === 'citizen') {
                $request->validate([
                    'email' => 'nullable|email|unique:users,email,' . $user->id,
                ]);
            }

            if (strtolower($request->type) === 'consultant') {
                $request->validate([
                    'years_of_experience' => 'required',
                    'current_profession' => 'nullable',
                    'general_info' => 'required',
                    'email' => 'required|email|unique:users,email,' . $user->id,
                    'nid' => 'required|unique:users,nid,' . $user->id,
                ]);

                if ($request->nid_front) {
                    $request->validate([
                        'nid_front' => 'required',
                    ]);
                }

                if ($request->nid_back) {
                    $request->validate([
                        'nid_back' => 'required',
                    ]);
                }

                if ($request->nid_front) {
                    $image_parts = explode(";base64,", $request->nid_front);
                    $imageType = explode("/", $image_parts[0])[1];
                    $unique = uniqid();
                    // return $imageType;
                    if (isset($image_parts[1])) {
                        $nid_front_image_path = FileHandler::uploadImage($request->nid_front, $request->type, $unique, $imageType, 'nid_front');
                        //   return $nid_front_image_path;
                        if (File::exists(public_path($user->nid_front))) {
                            File::delete(public_path($user->nid_front));
                        }
                    } else {
                        $nid_front_image_path = $user->nid_front;
                    }
                } else {
                    $nid_front_image_path = $user->nid_front;
                }

                if ($request->nid_back) {
                    $image_parts = explode(";base64,", $request->nid_back);
                    $imageType = explode("/", $image_parts[0])[1];
                    $unique = uniqid();
                    if (isset($image_parts[1])) {
                        $nid_back_image_path = FileHandler::uploadImage($request->nid_back, $request->type, $unique, $imageType, 'nid_back');

                        if (File::exists(public_path($user->nid_back))) {
                            File::delete(public_path($user->nid_back));
                        }
                    } else {
                        $nid_back_image_path = $user->nid_back;
                    }
                } else {
                    $nid_back_image_path = $user->nid_back;
                }

                $user->services()->sync($request->services);

            }

            if (strtolower($user->type) === 'consultant') {
                $user->update([
                    'approval' => 0,
                ]);
            }

            $user->update([
                'name' => $request->name,
                'phone' => $request->phone ?? $user->phone,
                'email' => $request->email ?? $user->email,
                'password' => $request->password ? Hash::make($request->password) : $user->password,
                'address' => $request->address ?? $user->address,
                'nid' => $request->nid ?? $user->nid,
                'dob' => $request->dob ?? $user->dob,
                // 'status' => $request->status ?? $user->status,
                'status' => 1,
                'gender' => strtolower($request->gender) ?? $user->gender,
                'profile_image' => $profile_image_path,
                'district_id' => $request->district_id ?? $user->district_id,
                'years_of_experience' => $request->years_of_experience ?? $user->years_of_experience,
                'current_profession' => $request->current_profession ?? $user->current_profession,
                'general_info' => $request->general_info ?? $user->general_info,
                'nid_front' => $nid_front_image_path ?? $user->nid_front,
                'nid_back' => $nid_back_image_path ?? $user->nid_back,
                'schedule' => $request->schedule ?? $user->schedule,
            ]);

            if (strtolower($user->type) === 'consultant') {
                if (is_array($request->experiances)) {
                    //  return $request->experiances;
                    foreach ($request->experiances as $key => $experiance) {
                        // return $experiance; // $experience part 1 ta full experience asbe
                        $existId = isset($request->experiances[$key]['id']);
                        //  return $existId; if any experience that's already have for this particular user
                        // then found 1 If that experiences was not found then 0(mane pore r dhukbei na)
                        if ($existId) {
                            $experianceId = $request->experiances[$key]['id'];
                            //  return $experianceId; In this $experianceId which experience already exists
                            // thats id will get in here.
                            $experiance = Experience::where('id', $experianceId)->first();
                            // return $experiance;  $experiance here return that exists experience
                            if ($experiance) {

                                $experiance->update([
                                    'institute_name' => $request->experiances[$key]['institute_name'],
                                    'address' => $request->experiances[$key]['address'],
                                    'designation' => $request->experiances[$key]['designation'],
                                    'department' => $request->experiances[$key]['department'] ?? '',
                                    'start_date' => $request->experiances[$key]['start_date'],
                                    'end_date' => $request->experiances[$key]['current_working'] ? '' : $request->experiances[$key]['end_date'],
                                    'user_id' => $user->id,
                                    'current_working' => $request->experiances[$key]['current_working'] ?? '',
                                ]);
                            }
                        } else {
                            Experience::create([
                                'institute_name' => $request->experiances[$key]['institute_name'],
                                'address' => $request->experiances[$key]['address'],
                                'designation' => $request->experiances[$key]['designation'],
                                'department' => $request->experiances[$key]['department'] ?? '',
                                'start_date' => $request->experiances[$key]['start_date'],
                                'end_date' => $request->experiances[$key]['current_working'] ? '' : $request->experiances[$key]['end_date'],
                                'user_id' => $user->id,
                                'current_working' => $request->experiances[$key]['current_working'] ?? '',
                            ]);
                        }
                    }
                }

                if (is_array($request->academics)) {
                    foreach ($request->academics as $key => $academic) {
                        //   return $request->academics[$key]['id'];
                        // return $academic['passing_year'];
                        $existId = isset($request->academics[$key]['id']);
                        //   return $existId; //if found then 1 paoua jabe
                        if ($existId) {
                            $academicsId = $request->academics[$key]['id'];
                            $academicFound = AcademicQualification::where('id', $academicsId)->first();

                            if (File::exists(public_path($academicFound->certification_copy))) {
                                File::delete(public_path($academicFound->certification_copy));
                            }
                        }

                        // return $request->academics[$key]['certification_copy'];

                        if ($request->academics[$key]['certification_copy']) {
                            $image_parts = explode(";base64,", $request->academics[$key]['certification_copy']);
                            $imageType = explode("/", $image_parts[0])[1];
                            $unique = uniqid();
                            if (isset($image_parts[1])) {
                                $certificateImage = FileHandler::uploadUniqueImage(
                                    $request->academics[$key]['certification_copy'],
                                    $request->type,
                                    $request->academics[$key]['education_level'],
                                    $imageType,
                                    $unique,
                                    'certificate'
                                );

                                // if (File::exists(public_path($academic->certification_copy))) {
                                //     File::delete(public_path($academic->certification_copy));
                                // }
                            } else {
                                $certificateImage = $academic['certification_copy'];
                            }
                        } else {

                            $certificateImage = $academic['certification_copy'];
                        }
                        // return $academicFound['certification_copy'];

                        if ($existId) {
                            if ($academicFound) {
                                $academicFound->update([
                                    'education_level' => $request->academics[$key]['education_level'],
                                    'institute_name' => $request->academics[$key]['institute_name'],
                                    'passing_year' => $request->academics[$key]['passing_year'],
                                    'certification_copy' => $request->academics[$key]['certification_copy'] ? $certificateImage : '',
                                    'user_id' => $user->id,
                                ]);
                            }
                        } else {

                            AcademicQualification::create([
                                'education_level' => $request->academics[$key]['education_level'],
                                'institute_name' => $request->academics[$key]['institute_name'],
                                'passing_year' => $request->academics[$key]['passing_year'],
                                'certification_copy' => $request->academics[$key]['certification_copy'] ? $certificateImage : '',
                                'user_id' => $user->id,
                            ]);
                        }
                    }
                }
            }

            $message = "Data Updated Successfully";
            DB::commit();
            return $this->responseSuccess(200, true, $message, $user);
        } catch (QueryException $e) {
            DB::rollBack();
        }
    }

    public function updatePassword(Request $request)
    {
        DB::beginTransaction();
        try {
            //  return "tttt";
            $user = User::findOrFail($request->id);
            if ($request->id) {

                $request->validate([
                    'old_password' => 'required|min:8',
                    'new_password' => 'required|min:8',
                ]);
                //$requestpassword=Hash::make($request->new_password);
                //  return $requestpassword;
                //  return auth()->user()->password;
                // if ($requestpassword == auth()->user()->password) {
                //     $message = "Your current password can't be new password";
                //     return $this->responseError(400, false, $message);
                // }
                ///  return auth()->user()->password;
                if ($user && Hash::check($request->old_password, $user->password)) {
                    $user->update([
                        'password' => Hash::make($request->new_password),
                    ]);

                    $message = "Password Updated Successfully";
                    DB::commit();
                    return $this->responseSuccess(200, true, $message, $user);
                } else {
                    $message = "Old Password Does Not Match";
                    return $this->responseError(403, false, $message);
                }
            }
        } catch (QueryException $e) {
            DB::rollBack();
        }
    }

    public function experienceDestroy($id)
    {
        // return "aaaaa";
        DB::beginTransaction();
        try {
            $experience = Experience::where('id', $id)->first();
            if ($experience != null) {
                $experience->delete();
                $message = "Experience Deleted Successfully";
                DB::commit();
                return $this->responseSuccess(200, true, $message, []);
            } else {
                $message = "No Data Found";
                return $this->responseError(404, false, $message);
            }
        } catch (QueryException $e) {
            DB::rollBack();
        }
    }

    public function academicQualificationDestroy($id)
    {
        DB::beginTransaction();
        try {
            $academicQualification = AcademicQualification::where('id', $id)->first();
            if ($academicQualification != null) {
                $academicQualification->delete();
                $message = "Academic Qualification Deleted Successfully";
                DB::commit();
                return $this->responseSuccess(200, true, $message, []);
            } else {
                $message = "No Data Found";
                return $this->responseError(404, false, $message);
            }
        } catch (QueryException $e) {
            DB::rollBack();
        }
    }

    public function profile($id)
    {
        if (!User::where('id', $id)->exists()){
            $message = "No Data Found";
            return $this->responseError(404, false, $message);
        }

        $user = User::with(
            'experiances',
            'academics',
            'services'
        )
            ->where('id', $id)
            ->first();

        $authUser = Auth::user();

        if ($id == $authUser->id) {
            $searchUserType = $user->type;
        }
        if ($id != $authUser->id) {
            $searchUserType = $user->type;
            $searchUserApproval = $user->approval;
        }

        if (Auth::check() && $authUser->id == $id) {

            if ($user != null) {
                $data = [
                    'user' => $user,
                    'role' => $user->getRoleNames(),
                    'permissions' => $user->getAllPermissions()->pluck('name'),
                ];

                $message = "";
                return $this->responseSuccess(200, true, $message, $data);
            }
        } elseif (Auth::check() && $authUser->type == 'admin') {

            if ($user != null) {
                $data = [
                    'user' => $user,
                    'role' => $user->getRoleNames(),
                    'permissions' => $user->getAllPermissions()->pluck('name'),
                ];

                $message = "";
                return $this->responseSuccess(200, true, $message, $data);
            }
        } elseif (Auth::check()
            && $authUser->id != $id
            && $authUser->type == 'citizen'
            && $searchUserType == 'consultant'
            && $searchUserApproval == 1) {

            if ($user != null) {
                $data = [
                    'user' => $user,
                    'role' => $user->getRoleNames(),
                    'permissions' => $user->getAllPermissions()->pluck('name'),
                ];

                $message = "";
                return $this->responseSuccess(200, true, $message, $data);
            }
        } else {
            $message = "No Data Found";
            return $this->responseError(404, false, $message);
        }
    }



    public function approved(Request $request)
    {
        DB::beginTransaction();
        try {
            $user = User::where(['id' => $request->approvalUserId, 'type' => 'consultant'])->first();
            if ($user) {
                $user->update([
                    'approval' => $request->approvalStatus,
                    'approved_by' => auth()->user()->id,
                ]);
                if ($request->approvalStatus == 2) {
                    $approvalStatus = 'Approved';
                } else if ($request->approvalStatus == 4) {
                    $approvalStatus = 'Deactivated';
                } else {
                    $approvalStatus = 'Rejected';
                }
                $message = "This Consultant Status Is Updated";
                DB::commit();
                return $this->responseSuccess(200, true, $message, $user);
            } else {
                $message = "Not Found Data";
                return $this->responseError(404, false, $message);
            }
        } catch (QueryException $e) {
            DB::rollBack();
        }
    }

    public function consultantList()
    {

        DB::beginTransaction();
        try {
            $consultants = User::active()->get();
            //  return $consultants;

            if ($consultants) {
                $message = "Consultant List Successfully Shown";
                DB::commit();
                return $this->responseSuccess(200, true, $message, $consultants);
            }
            //else {
            //     $message = "No Data Found";
            //     return $this->responseError(404, false, $message);
            // }
        } catch (QueryException $e) {
            DB::rollBack();
        }
    }

    public function activeUser()
    {
        $authUser = Auth::user();

        User::active()->where(['id' => $authUser->id])->update([
            'active_status' => !$authUser->active_status,
        ]);

        return $this->responseSuccess(200, true, '', []);
    }

    public function allDistricts()
    {
        $districts = DB::table('districts')->select(['id', 'name_bn', 'name_en'])->get();
        if (!empty($districts)) {
            $message = "Successfully Districts Data Shown";
            return $this->responseSuccess(200, true, $message, $districts);
        } else {
            $message = "Invalid Credentials";
            return $this->responseError(403, false, $message);
        }
    }

    public function activeStatusChange(Request $request, $consultant_id)
    {
        $consultantData = User::findOrFail($consultant_id);

        if($consultantData->approval == 2 || $consultantData->approval == 3){
            $consultantData->update([
                'active_status' => 0,
            ]);
        }

        else{
            if ($consultantData && $consultantData->active_status == 1) {
                $consultantData->update([
                    'active_status' => 0,

                ]);
            } else if ($consultantData && $consultantData->active_status == 0) {
                $consultantData->update([
                    'active_status' => 1,

                ]);
            }

        }

        if (!empty($consultantData)) {
            $message = "Consultant Active Status Successfully Updated";
            return $this->responseSuccess(200, true, $message, $consultantData);
        } else {
            $message = "Invalid credentials";
            return $this->responseError(403, false, $message);
        }
    }

    public function getDownload(Request $request)
    {
        $filepath = public_path($request->path);
        return response()->download($filepath);
    }

    public function profileImageUpdateMobile(Request $request)
    {

        $userExist = User::where('id', $request->id)
            ->select(['id', 'name', 'type', 'phone', 'profile_image'])
            ->first();

        if ($request->profile_image) {
            $image_parts = explode(";base64,", $request->profile_image);
            $imageType = explode("/", $image_parts[0])[1];
            if (isset($image_parts[1])) {
                $profile_image_path = FileHandler::uploadImage($request->profile_image, $userExist->type, $userExist->phone, $imageType, 'profile');
                if (File::exists($profile_image_path)) {
                    File::delete($profile_image_path);
                }
            }
        } else {
            $profile_image_path = $userExist->profile_image;
        }

        $userData = $userExist->update([
            'profile_image' => $profile_image_path,
        ]);
        if ($userData) {
            $message = "Profile Image Updated Successfully.";
            return $this->responseSuccess(200, true, $message, $userExist);
        }
    }
}
