<?php

namespace App\Http\Controllers\Common;

use App\Models\User;
use App\Models\Experience;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use App\Http\Helper\FileHandler;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use App\Models\AcademicQualification;
use Illuminate\Database\QueryException;


class ProfileController extends Controller
{
    use ResponseTrait;
    public function update(Request $request)
    {
        //  return $request->all();
        DB::beginTransaction();
        try {

            $user = User::findOrFail($request->id);
            // return $user;

            $request->validate([
                'name' => 'required|string|max:50',
                'phone' => 'max:15|min:11|regex:/(01)[0-9]{9}/|required|unique:users,phone,' . $user->id,
                'password' => 'nullable|min:8',
                'address' => 'required|max:50',
                'nid' => 'required|regex:/(?:\d{17}|\d{13}|\d{10})/|unique:users,nid,' . $user->id,
                'dob' => 'required|max:50',
                'gender' => 'required|max:10',
                'district_id' => 'required|max:50',
                // 'phone' => 'required|unique:users,phone,' . $user->id,
            ]);


            if ($request->profile_image) {
                $image_parts = explode(";base64,", $request->profile_image);
                if (isset($image_parts[1])) {
                    $profile_image_path = FileHandler::uploadImage($request->profile_image, $request->type, $request->phone, 'profile');
                    if (File::exists($profile_image_path)) {
                        File::delete($profile_image_path);
                    }
                } else {
                    $profile_image_path = $user->profile_image;
                }
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
                    'email' => 'required|email|unique:users,email,' . $user->id,
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
                    if (isset($image_parts[1])) {
                        $nid_front_image_path = FileHandler::uploadImage($request->nid_front, $request->type, $request->email, 'nid_front');
                        if (File::exists($nid_front_image_path)) {
                            File::delete($nid_front_image_path);
                        }
                    } else {
                        $nid_front_image_path = $user->nid_front;
                    }
                } else {
                    $nid_front_image_path = $user->nid_front;
                }

                if ($request->nid_back) {
                    $image_parts = explode(";base64,", $request->nid_back);
                    if (isset($image_parts[1])) {
                        $nid_back_image_path = FileHandler::uploadImage($request->nid_back, $request->type, $request->email, 'nid_back');
                        if (File::exists($nid_back_image_path)) {
                            File::delete($nid_back_image_path);
                        }
                    } else {
                        $nid_back_image_path = $user->nid_back;
                    }
                } else {
                    $nid_back_image_path = $user->nid_back;
                }

                $user->services()->sync($request->services);
            }

            $user->update([
                'name' => $request->name,
                'phone' => $request->phone ?? $user->phone,
                'email' => $request->email ?? $user->email,
                'password' => $request->password ? Hash::make($request->password) : $user->password,
                'address' => $request->address ?? $user->address,
                'nid' => $request->nid ?? $user->nid,
                'dob' => $request->dob ?? $user->dob,
                'status' => $request->status ?? $user->status,
                'gender' => $request->gender ?? $user->gender,
                'profile_image' => $profile_image_path ?? $user->profile_image,
                'district_id' => $request->district_id ?? $user->district_id,
                'years_of_experience' => $request->years_of_experience ?? $user->years_of_experience,
                'current_profession' => $request->current_profession ?? $user->current_profession,
                'nid_front' => $nid_front_image_path ?? $user->nid_front,
                'nid_back' => $nid_back_image_path ?? $user->nid_back,
                'schedule' => $request->schedule ?? $user->schedule
            ]);


            if (strtolower($user->type) === 'consultant') {
                if (is_array($request->experiances)) {
                    foreach ($request->experiances as $key => $experiance) {
                        $existId = isset($request->experiances[$key]['id']);

                        if ($existId) {
                            $experianceId = $request->experiances[$key]['id'];
                            $experiance = Experience::where('id', $experianceId)->first();

                            if ($experiance) {
                                $experiance->update([
                                    'institute_name' => $request->experiances[$key]['institute_name'],
                                    'designation' => $request->experiances[$key]['designation'],
                                    'department' => $request->experiances[$key]['department'],
                                    'start_date' => $request->experiances[$key]['start_date'],
                                    'end_date' => $request->experiances[$key]['end_date'],
                                    'user_id' => $user->id,
                                ]);
                            }
                        } else {
                            Experience::create([
                                'institute_name' => $request->experiances[$key]['institute_name'],
                                'designation' => $request->experiances[$key]['designation'],
                                'department' => $request->experiances[$key]['department'],
                                'start_date' => $request->experiances[$key]['start_date'],
                                'end_date' => $request->experiances[$key]['end_date'],
                                'user_id' => $user->id,
                            ]);
                        }
                    }
                }

                if (is_array($request->academics)) {
                    foreach ($request->academics as $key => $academic) {
                        $existId = isset($request->academics[$key]['id']);
                        if ($existId) {
                            $academicsId = $request->academics[$key]['id'];
                            $academic = AcademicQualification::where('id', $academicsId)->first();
                        }

                        // if ($request->academics[$key]['certification_copy']) {
                        //     //return $request->academics[$key]['certification_copy'];
                        //     $image_parts = explode(";base64,", $request->academics[$key]['certification_copy']);
                        //     $filename_path = md5(time() . uniqid()) . ".png";
                        //     if (isset($image_parts[1])) {
                        //         $decoded = base64_decode($image_parts[1]);
                        //         file_put_contents(public_path() . "/uploads/certificate/" . $filename_path, $decoded);
                        //         $certification_copy = "/uploads/certificate/" . $filename_path;
                        //         if (File::exists($certification_copy)) {
                        //             File::delete($certification_copy);
                        //         }
                        //     } else {
                        //         $certification_copy = $academic->certification_copy;
                        //     }
                        // } else {
                        //     $certification_copy = $academic->certification_copy;
                        // }

                        if ($request->academics[$key]['certification_copy']) {
                            $image_parts = explode(";base64,", $request->academics[$key]['certification_copy']);
                            if (isset($image_parts[1])) {
                                $certificateImage = FileHandler::uploadImage($request->academics[$key]['certification_copy'], $request->type, $request->email, 'certificate');
                                if (File::exists($certificateImage)) {
                                    File::delete($certificateImage);
                                }
                            } {
                                $certificateImage = $academic->certification_copy;
                            }
                        } else {
                            $certificateImage = $academic->certification_copy;
                        }


                        if ($existId) {
                            if ($academic) {
                                $academic->update([
                                    'education_level' => $request->academics[$key]['education_level'],
                                    'institute_name' => $request->academics[$key]['institute_name'],
                                    'passing_year' => $request->academics[$key]['passing_year'],
                                    'certification_copy' => $certificateImage,
                                    'user_id' => $user->id,
                                ]);
                            }
                        } else {
                            AcademicQualification::create([
                                'education_level' => $request->academics[$key]['education_level'],
                                'institute_name' => $request->academics[$key]['institute_name'],
                                'passing_year' => $request->academics[$key]['passing_year'],
                                'certification_copy' => $certificateImage,
                                'user_id' => $user->id,
                            ]);
                        }
                    }
                }
            }

            $message = $request->type . " Updated Succesfully";
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
                //     $message = "Your current password can't be with new password";
                //     return $this->responseError(400, false, $message);
                // }
                ///  return auth()->user()->password;
                if ($user && Hash::check($request->old_password, $user->password)) {
                    $user->update([
                        'password' => Hash::make($request->new_password)
                    ]);

                    $message = "Password Updated Succesfully";
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
                $message = "Deleted Succesfully";
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
                $message = "Deleted Succesfully";
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
        $user = User::with('experiances', 'academics', 'services')->where('id', $id)->first();
        //  return $user;
        if ($user != null) {
            $data = [
                'user' => $user,
                'role' => $user->getRoleNames(),
                'permissions' => $user->getAllPermissions()->pluck('name')
            ];

            $message = "";
            return $this->responseSuccess(200, true, $message, $data);
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
                    'approved_by' => auth()->user()->id
                ]);
                if ($request->approvalStatus == 2) {
                    $approvalStatus = 'Approved';
                } else if ($request->approvalStatus == 4) {
                    $approvalStatus = 'Deactivated';
                } else {
                    $approvalStatus = 'Rejected';
                }
                $message = "This Consultant " . $approvalStatus;
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
            return $consultants;

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
            $message = "Succesfully Data Shown";
            return $this->responseSuccess(200, true, $message, $districts);
        } else {
            $message = "Invalid credentials";
            return $this->responseError(403, false, $message);
        }
    }
}
