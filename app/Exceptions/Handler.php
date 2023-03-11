<?php

namespace App\Exceptions;

use Throwable;
use ErrorException;
use Illuminate\Http\Response;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Validation\ValidationException;

class Handler extends ExceptionHandler
{

    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
       // ValidationException::class,
    ];

    public function report(Throwable $exception)
    {
        parent::report($exception);
    }


    public function render($request, Throwable $exception)
    {
        info($exception->getMessage());

        if ($exception->getMessage() === "Unauthenticated.") {
            return response()->json(["status_code" => 401, "status" => false, 'message' => "Unauthenticated"], Response::HTTP_UNAUTHORIZED);
        }
        //return $exception->getMessage();
        if ($exception instanceof ModelNotFoundException) {
            return response()->json(["status_code" => 404, "status" => false, 'message' => "Not Found Your Targeted Data"], Response::HTTP_NOT_FOUND);
        }
        if ($exception instanceof QueryException) {
             return response()->json(["status_code" => 500, "status"=>false, 'message' => "Internal Server Error"], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        if ($exception instanceof MethodNotAllowedHttpException) {
            return response()->json(["status_code" => 405, "status" => false, 'message' => "Method Not Allowed"], Response::HTTP_METHOD_NOT_ALLOWED);
        }
        if ($exception instanceof ErrorException) {
            return response()->json(["status_code" => 500, "status" => false, 'message' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if ($exception instanceof \BadMethodCallException) {
            return response()->json(["status_code" => 500, "status" => false, 'message' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        // if ($exception instanceof PermissionAlreadyExists) {
        //     return response()->json(["status_code" => 422, 'message' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        // }
        if ($exception instanceof NotFoundHttpException) {
            return response()->json(["status_code" => 404, "status" => false, 'message' => "URL is not recognized"], Response::HTTP_NOT_FOUND);
        }
        if ($exception instanceof ValidationException) {
            return response()->json(["status_code" => 400, "status" => false, 'message' => $exception->getMessage(),'errors' => $exception->errors()], Response::HTTP_BAD_REQUEST);
        }

        return parent::render($request, $exception);
    }
}
