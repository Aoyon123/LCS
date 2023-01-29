<?php

namespace App\Http\Controllers\Consultant;

use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use Illuminate\Http\Response;
use App\Models\ConsultantRate;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use App\Http\Requests\ConsultantRateRequest;


class ConsultantRateController extends Controller
{
    use ResponseTrait;
    public function store(ConsultantRateRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = ConsultantRate::create([
                'citizen_id' => $request->citizen_id,
                'consultant_id' => $request->consultant_id,
                'rate' => $request->rate,
                'against_id' => $request->against_id,
            ]);

            DB::commit();
            $message = "ConsultantRate Created Successfull";
            return $this->responseSuccess(200, true, $message, $data);

        } catch (QueryException $e) {
            DB::rollBack();
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, $e->getMessage());
        }
    }

    public function rateCalculate(Request $request,$id)
    {
        $data = ConsultantRate::where('consultant_id', $id)->avg('rate');
        $roundData = round($data, 1);

        // $data = User::create([
        //     'rates' => ,

        // ]);
       //  return $data2;
        //  return $data;
        //  User::create([
        //     'rates' => $data
        // ]);
        //  return "aaaa";
        $message = "ConsultantRate Created Successfull";
        return $this->responseSuccess(200, true, $message, $roundData);
    }

}
