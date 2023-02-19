<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;

class CitizenController extends Controller
{
    use ResponseTrait;
    public function index()
    {
        DB::beginTransaction();

        try {
            $citizen = User::where('type', 'citizen')->get();
            if ($citizen != null) {
                $message = "Citizen Data Succesfully Shown";
                DB::commit();
                return $this->responseSuccess(200, true, $message, $citizen);
            } else {
                $message = "No Data Found";
                return $this->responseError(404, false, $message);
            }
        } catch (QueryException $e) {
            DB::rollBack();
        }
    }
}
