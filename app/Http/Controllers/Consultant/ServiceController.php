<?php

namespace App\Http\Controllers\Consultant;

use Illuminate\Support\Facades\DB;
use App\Models\Service;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use App\Http\Helper\FileHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceRequest;
use Illuminate\Database\QueryException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;

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

    public function store(Request $request)
    {

        DB::beginTransaction();
        try {
            if ($request->service_image) {
                $image_parts = explode(";base64,", $request->service_image);
                $unique =random_int(100000, 999999);
                $imageType = explode("/", $image_parts[0])[1];
                $type="service-icon";
                if (isset($image_parts[1])) {
                    $service_image_path = FileHandler::uploadImage($request->service_image,$type,$unique,$imageType,'service');
                }
            }

            $data = Service::create([
                'title' => $request->title,
                'description' => $request->description,
                'status' => $request->status,
                'service_image' => $service_image_path,
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

    public function update(Request $request, $id)
    {

        $serviceData = Service::findOrFail($id);

            if ($request->service_image) {

                $image_parts = explode(";base64,", $request->service_image);

                $imageType = explode("/", $image_parts[0])[1];
               // return explode("/", $request->title)[0];
                $type="service-icon";
                if (isset($image_parts[1])) {
                    $service_image_path = FileHandler::uploadImage($request->service_image,$type,$request->id,$imageType,'service');

                    if (File::exists($service_image_path)) {
                        File::delete($service_image_path);
                    }
                } else {
                    $service_image_path = $serviceData->service_image;
                }
            }

            if ($serviceData) {
                $serviceData->update([
                    'title' => $request->title ?? $serviceData->title,
                    'description' => $request->description ?? $serviceData->description,
                    'status' => $request->status ?? $serviceData->status,
                    'service_image' => $service_image_path ?? $serviceData->service_image,
                    'remark' => $request->remark ?? $serviceData->remark,
                ]);
                DB::commit();
                $message="Service Updated Succesfully";
                return $this->responseSuccess(200, true, $message, $serviceData);
            }
            else {
                $message = "No Data Found";
                return $this->responseError(404, false, $message);
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
