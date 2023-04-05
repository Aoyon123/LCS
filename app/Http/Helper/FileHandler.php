<?php

namespace App\Http\Helper;

use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;


class FileHandler
{
    public static function uploadImage($image, $type, $unique, $imageType, $path)
    {
        $image_parts = explode(";base64,", $image);
        $imageType = explode("/", $image_parts[0])[1];
        $filename_path = $type . '_' . $unique . ".".$imageType;
        if (isset($image_parts[1])) {
            $image_path = "/uploads/$path/$filename_path";
            $decoded = base64_decode($image_parts[1]);
            file_put_contents(public_path() . $image_path, $decoded);
            return $image_path;
        }
    }


    public static function uploadUniqueImage($image, $type, $education_level, $imageType, $unique, $path)
    {
        $image_parts = explode(";base64,", $image);
        $imageType = explode("/", $image_parts[0])[1];
        $filename_path = $type . '_' . $unique . '_' . $education_level . "." . $imageType;
        if (isset($image_parts[1])) {
            $image_path = "/uploads/$path/$filename_path";
            $decoded = base64_decode($image_parts[1]);
            file_put_contents(public_path() . $image_path, $decoded);
            return $image_path;
        }
    }

    public static function upload4ParamsImage($image, $type, $unique, $path)
    {
        $image_parts = explode(";base64,", $image);
        $filename_path = $type . '_' . $unique . ".png";
        if (isset($image_parts[0])) {
            $image_path = "/uploads/$path/$filename_path";
            $decoded = base64_decode($image_parts[0]);
            file_put_contents(public_path() . $image_path, $decoded);
            return $image_path;
        }
    }

    public static function uploadFile($file, $extension, $unique, $path)
    {
        $file_parts = explode(";base64,", $file);
        $filename_path = $unique . $extension;
        if (isset($file_parts[1])) {
            $file_path = "/uploads/$path/$filename_path";
            $decoded = base64_decode($file_parts[1]);
            file_put_contents(public_path() . $file_path, $decoded);
            return $file_path;
        }
    }

}
