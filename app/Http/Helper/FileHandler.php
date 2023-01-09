<?php

namespace App\Http\Controllers\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class FileHandler
{
    public static function upload($image, $path, $size = null, $prefix = null)
    {
        $prefix = isset($prefix) ? $prefix : time();

        $image_name = $prefix . '-' . $size['width'] . 'x' . $size['height'] . '-' . $image->getClientOriginalName();

        $image_path = "uploads/$path/$image_name";

        $resized_image = Image::make($image)->resize($size['width'], $size['height'])->stream();

        Storage::disk('public')->put($image_path, $resized_image);
        return $image_path;
    }

    public static function uploadImage($image,$path,$size=null){
          
    }
            // if ($request->file('image')) {
            //     $image_path = FileHandler::upload($request->image, 'user_images', ['width' => '84', 'height' => '84']);
            //     $user->image()->create([
            //         'url' => Storage::url($image_path),
            //         'base_path' => $image_path,
            //     ]);
            // }
    // if ($request->profile_image) {
    //     $image_parts = explode(";base64,", $request->profile_image);
    //     $filename_path = md5(time() . '_' . $request->phone) . ".png";
    //     if (isset($image_parts[1])) {
    //         $decoded = base64_decode($image_parts[1]);
    //         file_put_contents(public_path() . "/uploads/profile/" . $filename_path, $decoded);
    //         $profile_image_path = "/uploads/profile/" . $filename_path;
    //         if (File::exists($profile_image_path)) {
    //             File::delete($profile_image_path);
    //         }
    //     } else {
    //         $profile_image_path = $user->profile_image;
    //     }

    // } else {
    //     $profile_image_path = $user->profile_image;
    // }




    public static function delete($path)
    {
        if (Storage::exists($path)) {
            Storage::delete($path);
        }
    }
}
