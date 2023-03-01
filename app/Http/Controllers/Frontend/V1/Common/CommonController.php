<?php

namespace App\Http\Controllers\Frontend\V1\Common;

use App\Models\User;
use App\Models\Service;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class CommonController extends Controller
{
    use ResponseTrait;
    public function dashboard()
    {
        //  $service = DB::table('services')->get();
        $service = Service::activeservicelist()->take(5)->get();
      //  return $service;
        $consultants_selected_fields = ['id', 'name', 'phone', 'email', 'address', 'code', 'type', 'profile_image','district', 'gender', 'rates', 'active_status', 'years_of_experience', 'schedule'];

        $active = User::with(
            [
                'experianceLatest:user_id,institute_name',
                'academicLatest:user_id,education_level',
                'serviceLatest',
            ]
        )->select($consultants_selected_fields)->approval()->consultant()->status()->active()->get();


        $topRated = User::with(
            [
                'experianceLatest:user_id,institute_name',
                'academicLatest:user_id,education_level',
                'serviceLatest',
            ]
        )->select($consultants_selected_fields)->orderBy('rates', 'DESC')->take(6)->status()->approval()->consultant()->get();
        if ($topRated && $service && $active) {
            $data = [
                'active' => $active,
                'topRated' => $topRated,
                'service' => $service
            ];
        }

        if (!empty($data)) {
            $message = "Succesfully Data Shown";
            return $this->responseSuccess(200, true, $message, $data);
        } else {
            $message = "Invalid credentials";
            return $this->responseError(403, false, $message);
        }
    }

    public function activeServiceList()
    {
        $service = Service::activeservicelist()->get();

        if (!empty($service)) {
            $message = "Succesfully Data Shown";
            return $this->responseSuccess(200, true, $message, $service);
        } else {
            $message = "Invalid credentials";
            return $this->responseError(403, false, $message);
        }
    }

    public function allDistricts()
    {
        $districts = DB::table('districts')->select(['id','name_bn','name_en'])->get();
        if (!empty($districts)) {
            $message = "Succesfully Data Shown";
            return $this->responseSuccess(200, true, $message, $districts);
        } else {
            $message = "Invalid credentials";
            return $this->responseError(403, false, $message);
        }
    }


}
