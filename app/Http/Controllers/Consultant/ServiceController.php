<?php

namespace App\Http\Controllers\Consultant;

use App\Http\Controllers\Controller;
use App\Http\Helper\FileHandler;
use App\Models\Service;
use App\Traits\ResponseTrait;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
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
                $unique = random_int(100000, 999999);
                $imageType = explode("/", $image_parts[0])[1];
                $type = "service-icon";
                if (isset($image_parts[1])) {
                    $service_image_path = FileHandler::uploadImage($request->service_image, $type, $unique, $imageType, 'service');
                }
            }
            // return $service_image_path;

            $data = Service::create([
                'title' => $request->title,
                'description' => $request->description,
                'status' => $request->status,
                'service_image' => $service_image_path ?? '',
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

    public function serviceUpdate(Request $request, $id)
    {

        $serviceData = Service::findOrFail($id);

        if ($request->service_image) {

            $image_parts = explode(";base64,", $request->service_image);

            $imageType = explode("/", $image_parts[0])[1];
            $unique = random_int(100000, 999999);
            $type = "service-icon";

            if (isset($image_parts[1])) {
                $service_image_path = FileHandler::uploadImage($request->service_image, $type, $unique, $imageType, 'service');

                if (File::exists(public_path($serviceData->service_image))) {
                    File::delete(public_path($serviceData->service_image));
                }
            } else {
                $service_image_path = $serviceData->service_image;
            }
        } else {
            $service_image_path = $serviceData->service_image;
        }

        if ($serviceData) {
            $serviceData->update([
                'title' => $request->title ?? $serviceData->title,
                'description' => $request->description ?? '',
                'status' => $request->status ?? $serviceData->status,
                'service_image' => $service_image_path,
                'remark' => $request->remark ?? $serviceData->remark,
            ]);
            DB::commit();
            $message = "Service Updated Succesfully";
            return $this->responseSuccess(200, true, $message, $serviceData);
        } else {
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
