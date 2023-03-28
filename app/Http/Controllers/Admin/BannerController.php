<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Banner;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use Illuminate\Http\Response;
use App\Http\Helper\FileHandler;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\BannerRequest;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\File;

class BannerController extends Controller
{
    use ResponseTrait;

    public function bannerList()
    {
        $data = Banner::activeBanner()->get();
        if (!empty($data)) {
            $message = "Succesfully Data Shown";
            return $this->responseSuccess(200, true, $message, $data);
        } else {
            $message = "Invalid credentials";
            return $this->responseError(403, false, $message);
        }
    }


    public function store(BannerRequest $request)
    {
        DB::beginTransaction();
        try {

            if ($request->image) {
                $page_location_name = str_replace(' ', '', $request->page_location);
                $image_parts = explode(";base64,", $request->image);
                if (isset($image_parts[0])) {
                    $banner_image_path = FileHandler::uploadfaqImage($request->image, $page_location_name, $request->id, 'bannerImage');
                }
            }

            $data = Banner::create([
                'status' => $request->status,
                'page_location' => $request->page_location,
                'image' => $banner_image_path,
            ]);

            DB::commit();
            $message = "Banner Created Successfull";
            return $this->responseSuccess(200, true, $message, $data);

        } catch (QueryException $e) {
            DB::rollBack();
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, $e->getMessage());
        }
    }

    public function update(BannerRequest $request, $id)
    {
        $bannerData = Banner::findOrFail($id);

        DB::beginTransaction();
        try {
            if ($request->image) {
                $image_parts = explode(";base64,", $request->image);
                if (isset($image_parts[0])) {
                    $banner_image_path = FileHandler::uploadfaqImage($request->image, $bannerData->page_location, $request->id, 'bannerImage');
                    if (File::exists($banner_image_path)) {
                        File::delete($banner_image_path);
                    }
                } else {
                    $banner_image_path = $bannerData->image;
                }
            }

            if ($bannerData) {
                $bannerData->update([
                    'status' => $request->status ?? $bannerData->status,
                    'page_location' => $request->page_location ?? $bannerData->page_location,
                    'image' => $request->image ?? $bannerData->image,
                ]);

                $message = "Banner Data Updated Succesfully";
                DB::commit();
                return $this->responseSuccess(200, true, $message, $bannerData);
            } else {
                $message = "No Data Found";
                return $this->responseError(404, false, $message);
            }
        } catch (QueryException $e) {
            DB::rollBack();
        }
    }

}
