<?php

namespace App\Http\Controllers\Frontend\V1\Consultant;

use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use App\Http\Controllers\Controller;

class ConsultantController extends Controller
{

    use ResponseTrait;
    public function index()
    {
        $data = User::where(['type' => 'consultant', 'status' => 1, 'approval' => 2])->get();

        if (!empty($data)) {
            $message = "Succesfully Data Shown";
            return $this->responseSuccess(200, true, $message, $data);
        } else {
            $message = "Invalid credentials";
            return $this->responseError(403, false, $message);
        }
    }
}
