<?php

namespace App\Http\Controllers\Consultant;

use Illuminate\Support\Facades\DB;
use App\Models\Service;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceRequest;
use Illuminate\Database\QueryException;
use Illuminate\Http\Response;

class ServiceController extends Controller
{
    use ResponseTrait;

    public function allServices()
    {
        $data = Service::all();
        if (!empty($data)) {
            $message = "Succesfully Data Shown";
            return $this->responseSuccess(200, true, $message, $data);
        } else {
            $message = "Invalid credentials";
            return $this->responseError(403, false, $message);
        }
    }

    public function index()
    {
        $data = Service::all();
       // return $data;
        if (!empty($data)) {
            $message = "Succesfully Data Shown";
            return $this->responseSuccess(200, true, $message, $data);
        } else {
            $message = "Invalid credentials";
            return $this->responseError(403, false, $message);
        }
    }




    public function store(ServiceRequest $request)
    {

        DB::beginTransaction();
        try {
            $data = Service::create([
                'title' => $request->title,
                'description' => $request->description,
                'status' => $request->status,
                'remark' => $request->remark,
            ]);

            DB::commit();
            $message = "Service Created Successfull";
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
            $data = Service::findOrFail($id);
            if ($data) {
                $message = "Service Found";
                DB::commit();
                return $this->responseSuccess(200, true, $message, $data);
            } else {
                $message = "No Data Found";
                return $this->responseError(404, false, $message);
            }
        } catch (QueryException $e) {
            DB::rollBack();
        }
    }

    public function update(ServiceRequest $request, $id)
    {

        $input = Service::findOrFail($id);
        DB::beginTransaction();
        try {
            if ($input) {
                $input->title = $request['title'];
                $input->description = $request['description'];
                $input->status = $request['status'];
                $input->remark = $request['remark'];
                $input->save();
                $message = "Service Updated Succesfully";
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

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $user = Service::findOrFail($id);
            if ($user) {
                $user->delete();
                $message = "Services Deleted Succesfully";
                DB::commit();
                return $this->responseSuccess(200, true, $message, []);
            }
        } catch (QueryException $e) {
            DB::rollBack();
        }
    }
}
