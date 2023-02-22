<?php

namespace App\Http\Controllers\Common;

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

    public function allMessage($purposeId)
    {
        $data = [];
        $data['messages'] = Conversation::with(['sender:id,name,profile_image', 'receiver:id,name,profile_image'])
            ->where(['purpose_id' => $purposeId])
            ->latest()
            ->limit(20)
            ->get()
            ->sortBy('id')->values()->all();

        $data['offset'] = 0;

        $message = "Conversion Data Shown Successfull";
        return $this->responseSuccess(200, true, $message, $data);
        //  ->where(['id' => $id, 'seen_status' => 0])
        // ->first();
        //  ->get();
        // return $data;
        // if ($data) {
        //     // $user->update([
        //     //     'approval' => $request->approvalStatus,
        //     //     'approved_by' => auth()->user()->id
        //     // ]);
        // }
    }

    public function seeMoreMessage($purposeId, $offset)
    {
        $data = [];
        $data['messages'] = Conversation::with(['sender:id,name,profile_image', 'receiver:id,name,profile_image'])
            ->where(['purpose_id' => $purposeId])
            ->latest()
            ->offset($offset)
            ->limit(20)
            ->get()
            ->sortBy('id')->values()->all();

        $data['offset'] = $offset;
        $message = "More Conversion Data Shown Successfull";
        return $this->responseSuccess(200, true, $message, $data);

        // if ($data) {
        //     // $user->update([
        //     //     'approval' => $request->approvalStatus,
        //     //     'approved_by' => auth()->user()->id
        //     // ]);
        // }
    }

    public function store(ConversationRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = Conversation::create([
                'sender_id' => auth()->user()->id,
                'receiver_id' => $request->receiver_id,
                'message' => $request->message,
                'purpose_id' => $request->purpose_id,
                'purpose_type' => $request->purpose_type,
                'time' => Carbon::now()->toDateTimeString(),
                'status' => 0,
                'seen_status' => 0,
            ]);
            $data = Conversation::with(['sender:id,name,profile_image', 'receiver:id,name,profile_image'])
                ->where(['id' => $data->id])
                ->first();

            DB::commit();
            $message = "Conversation Created Successfull";
            return $this->responseSuccess(200, true, $message, $data);

        } catch (QueryException $e) {
            DB::rollBack();
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $conversationsData = Conversation::findOrFail($id);
            //return $conversationsData;
            $conversationsData->delete();

            Conversation::find($id)->update(['is_delete' => 1]);

            $message = "Case Deleted Succesfully";
            DB::commit();
            return $this->responseSuccess(200, true, $message, []);
        } catch (QueryException $e) {
            DB::rollBack();
        }
    }
}
