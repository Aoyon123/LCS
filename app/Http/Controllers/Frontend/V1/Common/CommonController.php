<?php

namespace App\Http\Controllers\Frontend\V1\Common;

use App\Models\User;
use App\Models\Service;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class CommonController extends Controller
{
    use ResponseTrait;
    public function dashboard()
    {
        $service = DB::table('services')->get();
        $consultants_selected_fields = ['id', 'name', 'phone', 'email', 'address', 'code', 'type', 'profile_image', 'gender', 'rates', 'active_status', 'years_of_experience', 'schedule'];
        $consultant = User::with(
            [
                'experianceLatest:user_id,institute_name',
                'academicLatest:user_id,education_level',
                'serviceLatest',
            ]
        )->select($consultants_selected_fields)->active()->get();
        if ($consultant && $service) {
            $data = [
                'topRated' => $consultant,
                'active' => $consultant,
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
        $consultant = Service::activeservicelist()->get();


        if (!empty($consultant)) {
            $message = "Succesfully Data Shown";
            return $this->responseSuccess(200, true, $message, $consultant);
        } else {
            $message = "Invalid credentials";
            return $this->responseError(403, false, $message);
        }
    }
}
