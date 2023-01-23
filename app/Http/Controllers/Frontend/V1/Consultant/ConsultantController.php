<?php

namespace App\Http\Controllers\Frontend\V1\Consultant;

use App\Models\User;
use App\Models\Service;
use App\Traits\ResponseTrait;
use App\Http\Controllers\Controller;

class ConsultantController extends Controller
{

    use ResponseTrait;
    public function index()
    {
        $service = Service::all();
        $user = User::active()->get();

        if ($user) {
            $data = [
                'topRated' => $user,
                'active' => $user,
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
}
