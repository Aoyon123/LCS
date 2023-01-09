<?php

namespace App\Http\Controllers\Common;

use random;
//use Illuminate\Http\File;
use App\Models\User;
use App\Models\Experience;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\ResponseTrait;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use App\Models\AcademicQualification;
use App\Http\Requests\RegisterRequest;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpFoundation\Session\Session;

class ProfileController extends Controller
{
    use ResponseTrait;
    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            $user = User::findOrFail($request->id);
            $request->validate([
                'name' => 'required|string|max:50',
                'phone' => 'max:15|min:11|regex:/(01)[0-9]{9}/',
                'password' => 'nullable|min:8',
                'address' => 'required|max:50',
                'nid' => 'required|max:50',
                'dob' => 'required|max:50',
                'gender' => 'required|max:10',
            ]);

            if ($request->hasFile('profile_image')) {
                $request->validate([
                    'profile_image' => 'max:2024|mimes:jpeg,jpg,png,gif',
                ]);
            }
            // $image = $request->file('profile_image');
            // $image = str_replace('data:image/png;base64,', '', $image);
            // $imageName = $request->type . '_' . $request->phone . '.' . $image->getClientOriginalExtension();
            // $destinationPath = public_path('uploads/profile/');
            // $image->move($destinationPath, $imageName);
            // $image_path_save = '/uploads/profile/' . $imageName;

            if ($request->profile_image) {
                $image_parts = explode(";base64,", $request->profile_image);
                $filename_path = md5(time() . '_' . $request->phone) . ".png";
                if (isset($image_parts[1])) {
                    $decoded = base64_decode($image_parts[1]);
                    file_put_contents(public_path() . "/uploads/profile/" . $filename_path, $decoded);
                    $profile_image_path = "/uploads/profile/" . $filename_path;
                    if (File::exists($profile_image_path)) {
                        File::delete($profile_image_path);
                    }
                } else {
                    $profile_image_path = $user->profile_image;
                }

            } else {
                $profile_image_path = $user->profile_image;
            }


            if ($request->type === 'consultant') {
                $request->validate([
                    'years_of_experience' => 'required',
                    'current_profession' => 'nullable',
                    'email' => 'required|email|unique:users,email,' . $user->id,
                ]);

                if ($request->hasFile('nid_front')) {
                    $request->validate([
                        'nid_front' => 'required|max:2024|mimes:jpeg,jpg,png,gif',
                    ]);
                }

                if ($request->hasFile('nid_back')) {
                    $request->validate([
                        'nid_back' => 'required|max:2024|mimes:jpeg,jpg,png,gif',
                    ]);
                }
                //nid_front
                if ($request->nid_front) {
                    $image_parts = explode(";base64,", $request->nid_front);
                    $filename_path = md5(time() . uniqid()) . ".png";
                    if (isset($image_parts[1])) {
                        $decoded = base64_decode($image_parts[1]);
                        file_put_contents(public_path() . "/uploads/nid_front/" . $filename_path, $decoded);
                        $nid_front_image_path = "/uploads/nid_front/" . $filename_path;
                        if (File::exists($nid_front_image_path)) {
                            File::delete($nid_front_image_path);
                        }
                    } else {
                        $nid_front_image_path = $user->nid_front;
                    }
                } else {
                    $nid_front_image_path = $user->nid_front;
                }

                //nid_back
                if ($request->nid_back) {
                    $image_parts = explode(";base64,", $request->nid_back);
                    $filename_path = md5(time() . uniqid()) . ".png";
                    if (isset($image_parts[1])) {
                        $decoded = base64_decode($image_parts[1]);
                        file_put_contents(public_path() . "/uploads/nid_back/" . $filename_path, $decoded);
                        $nid_back_image_path = "/uploads/nid_back/" . $filename_path;
                        if (File::exists($nid_back_image_path)) {
                            File::delete($nid_back_image_path);
                        }
                    } else {
                        $nid_back_image_path = $user->nid_back;
                    }
                } else {
                    $nid_back_image_path = $user->nid_back;
                }

            }

            $user->update([
                'name' => $request->name ?? $user->name,
                'phone' => $request->phone ?? $user->phone,
                'email' => $request->email ?? $user->email,
                'password' => $request->password ? Hash::make($request->password) : $user->password,
                'address' => $request->address ?? $user->address,
                'nid' => $request->nid ?? $user->nid,
                'dob' => $request->dob ?? $user->dob,
                'status' => $request->status ?? $user->status,
                'gender' => $request->gender ?? $user->gender,
                'profile_image' => $profile_image_path,
                'years_of_experience' => $request->years_of_experience ?? $user->years_of_experience,
                'current_profession' => $request->current_profession ?? $user->current_profession,
                'nid_front' => $nid_front_image_path,
                'nid_back' => $nid_back_image_path,
            ]);

            if ($user->type === 'consultant') {
                if (is_array($request->experiances)) {
                    // $user->experiances()->delete();
                    foreach ($request->experiances as $key => $experiance) {
                        $existId = isset($request->experiances[$key]['id']);
                        // return $existId;
                        if ($existId) {
                            $experianceId = $request->experiances[$key]['id'];
                            $experiance = Experience::where('id', $experianceId)->first();

                            if ($experiance) {
                                // info($request->experiances[$key]['designation']);
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
                        if ($request->academics[$key]['certification_copy']) {
                            $image_parts = explode(";base64,", $request->academics[$key]['certification_copy']);
                            $filename_path = md5(time() . uniqid()) . ".png";
                            if (isset($image_parts[1])) {
                                $decoded = base64_decode($image_parts[1]);
                                file_put_contents(public_path() . "/uploads/nid_back/" . $filename_path, $decoded);
                                $certification_copy = "/uploads/nid_back/" . $filename_path;
                                if (File::exists($certification_copy)) {
                                    File::delete($certification_copy);
                                }
                            } else {
                                $certification_copy = $academic->certification_copy;
                            }
                        } else {
                            $certification_copy = $academic->certification_copy;
                        }

                        if ($existId) {
                            if ($academic) {
                                $academic->update([
                                    'education_level' => $request->academics[$key]['education_level'],
                                    'institute_name' => $request->academics[$key]['institute_name'],
                                    'passing_year' => $request->academics[$key]['passing_year'],
                                    'certification_copy' => $certification_copy,
                                    'user_id' => $user->id,
                                ]);
                            }
                        } else {
                            AcademicQualification::create([
                                'education_level' => $request->academics[$key]['education_level'],
                                'institute_name' => $request->academics[$key]['institute_name'],
                                'passing_year' => $request->academics[$key]['passing_year'],
                                'certification_copy' => $certification_copy,
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

            $user = User::findOrFail($request->id);
            if ($request->id) {

                $request->validate([
                    'old_password' => 'required|min:8',
                    'new_password' => 'required|min:8',
                ]);
                //  return $user->password;
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
        DB::beginTransaction();
        try {
            $experience = Experience::where('id', $id)->first();
            $experience->delete();
            $message = "Deleted Succesfully";
            DB::commit();
            return $this->responseSuccess(200, true, $message, []);

        } catch (QueryException $e) {
            DB::rollBack();
        }
    }

    public function academicQualificationDestroy($id)
    {
        DB::beginTransaction();
        try {
            $academicQualification = AcademicQualification::where('id', $id)->first();
            $academicQualification->delete();
            $message = "Deleted Succesfully";
            DB::commit();
            return $this->responseSuccess(200, true, $message, []);

        } catch (QueryException $e) {
            DB::rollBack();
        }
    }

}
