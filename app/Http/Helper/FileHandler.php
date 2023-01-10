<?php

namespace App\Http\Controllers\Helpers;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;


class FileHandler
{
    public static function uploadImage($image, $type, $unique, $path,$size = null)
    {
        $image_parts = explode(";base64,", $image);
        $filename_path = $type . $size['width'] . 'x' . $size['height'] . '_' . $unique . ".png";
        if(isset($image_parts[1])){
            $image_path = "uploads/$path/$filename_path";

            $decoded = Image::make($image)->resize($size['width'], $size['height'])->base64_decode($image_parts[1]);
            Storage::disk('public')->put($image_path, $decoded);
            return $image_path;
        }
    }
}
