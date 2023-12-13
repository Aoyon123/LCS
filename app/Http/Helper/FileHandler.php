<?php

namespace App\Http\Helper;

use Spatie\Image\Image;

class FileHandler
{

    // self::imageOptimize(public_path($file_path));

    public static function uploadImage($image, $type, $unique, $imageType, $path)
    {
        $image_parts = explode(";base64,", $image);
        $imageType = explode("/", $image_parts[0])[1];
        $filename_path = $type . '_' . $unique . "." . $imageType;
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

    public static function imageOptimize($pathToImage)
    {
        // Load the image from the specified path
        Image::load($pathToImage)
        // Set the quality of the image to 5 (you might adjust this based on your requirements)
            ->quality(100)
        // Save the optimized image (this might overwrite the original image, depending on the library)
            ->save();
    }

}
