<?php

namespace App\Http\Helper;

use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;


class FileHandler
{
    public static function uploadImage($image, $type, $unique, $path)
    {
        $image_parts = explode(";base64,", $image);
        $filename_path = $type . '_' . $unique . ".png";
        if (isset($image_parts[1])) {
            $image_path = "/uploads/$path/$filename_path";
            $decoded = base64_decode($image_parts[1]);
            file_put_contents(public_path() . $image_path, $decoded);
            return $image_path;
        }

        // if ($request->academics[$key]['certification_copy']) {
        //     //return $request->academics[$key]['certification_copy'];
        //     $image_parts = explode(";base64,", $request->academics[$key]['certification_copy']);
        //     $filename_path = md5(time() . uniqid()) . ".png";
        //     if (isset($image_parts[1])) {
        //         $decoded = base64_decode($image_parts[1]);
        //         file_put_contents(public_path() . "/uploads/certificate/" . $filename_path, $decoded);
        //         $certification_copy = "/uploads/certificate/" . $filename_path;
        //         if (File::exists($certification_copy)) {
        //             File::delete($certification_copy);
        //         }
        //     } else {
        //         $certification_copy = $academic->certification_copy;
        //     }
        // }

    }

    public static function uploadFile($file, $unique, $path)
    {
        $file_parts = explode(";base64,", $file);
        $filename_path = 'case' . '_' . $unique . ".pdf";
        if (isset($file_parts[1])) {
            $file_path = "/uploads/$path/$filename_path";
            $decoded = base64_decode($file_parts[1]);
            file_put_contents(public_path() . $file_path, $decoded);
            return $file_path;
        }
    }

}
