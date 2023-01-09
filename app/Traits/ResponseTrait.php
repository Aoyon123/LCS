<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ResponseTrait
{

    /**
     *
     * @param array|object $data
     * @param string $message
     * @return JsonResponse
     */
    public function responseSuccess($status_code, $status, $message = "", $data): JsonResponse
    {
        return response()->json([
            'status_code'=>$status_code,
            'status' => $status,
            'message' => $message,
            'data' => $data,
        ]);
    }


    /**
     *Error Response
     * @param array|object $errors
     * @param string $message
     * @return JsonResponse
     */
    public function responseError($status_code,$status, $message = "Something Went Wrong"): JsonResponse
    {
        return response()->json([
            'status_code'=>$status_code,
            'status' => $status,
            'message' => $message,

        ]);
    }

    public function responseDone($status_code,$status,$message): JsonResponse
    {
        return response()->json([
            'status_code'=>$status_code,
            'status' => $status,
            'message' => $message,
        ]);
    }

    public function responseUpdate($status_code,$status,$data): JsonResponse{
        
    }
}
