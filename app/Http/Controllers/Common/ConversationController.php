<?php

namespace App\Http\Controllers\Common;

use App\Models\LcsCase;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Traits\ResponseTrait;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Http\Requests\ConversationRequest;

class ConversationController extends Controller
{
    use ResponseTrait;
    public function store(ConversationRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $type = auth()->user()->type;
            //     if($type === 'citizen'){
            //     $data = Conversation::create([
            //         'citizen_id' => auth()->user()->id,
            //         'consultant_id' => $request->consultant_id,
            //         'case_message' => $request->case_message,
            //         'case_id' => $request->case_id,
            //         'time' => Carbon::now()->toDateTimeString(),
            //         // 'seen_status' => '',
            //         // 'status' => '',
            //         // 'is_delete' =>''

            //     ]);
            // }
            // else if($type === 'consultant'){
            //     $data = Conversation::create([
            //         'citizen_id' => $request->citizen_id,
            //         'consultant_id' => auth()->user()->id,
            //         'case_message' => $request->case_message,
            //         'case_id' => $request->case_id,
            //         'time' => Carbon::now()->toDateTimeString(),
            //         // 'seen_status' => '',
            //         // 'status' => '',
            //         // 'is_delete' =>''

            //     ]);
            // }

            $caseData = LcsCase::where('id', $id)->first();
            // return $caseData;
            $data = Conversation::create([
                'citizen_id' => $caseData->citizen_id,
                'consultant_id' => $caseData->consultant_id,
                'case_message' => $request->case_message,
                'case_id' => $caseData->id,
                'time' => Carbon::now()->toDateTimeString(),
                // 'seen_status' => '',
                 'status' => $request->status,
                // 'is_delete' =>''
            ]);

            DB::commit();
            $message = "Conversation Created Successfull";
            return $this->responseSuccess(200, true, $message, $data);

        } catch (QueryException $e) {
            DB::rollBack();
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, $e->getMessage());
        }


    }
}
