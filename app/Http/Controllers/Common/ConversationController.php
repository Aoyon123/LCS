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
        $this->seenMessage($purposeId);
        $data = [];
        $data['messages'] = Conversation::with(
            ['sender:id,name,profile_image', 'receiver:id,name,profile_image']
        )
            ->where(['purpose_id' => $purposeId])
            ->latest()
            ->limit(10)
            ->get()
            ->sortBy('id')
            ->values()
            ->all();

        $data['offset'] = 0;
        $message = "Conversion Data Shown Successfull";
        return $this->responseSuccess(200, true, $message, $data);

    }

    public function seenMessage($purposeId)
    {
        //    $data=Conversation::where(['purpose_id' => $purposeId])
        //    ->where(['receiver_id' => auth()->user()->id, 'seen_status' => 0])
        //    ->latest()
        //    ->limit(200)
        //    ->update(['seen_status' => 1]);

        Conversation::where(['purpose_id' => $purposeId])
            ->where(['receiver_id' => auth()->user()->id, 'seen_status' => 0])
            ->latest()
            ->limit(200)
            ->update(['seen_status' => 1]);

        // $message = "Conversion Message Seen Successfully";
        // return $this->responseSuccess(200, true, $message, $data);
    }


    public function seeMoreMessage($purposeId, $offset)
    {
        $data = [];
        $data['messages'] = Conversation::with(
            ['sender:id,name,profile_image', 'receiver:id,name,profile_image']
        )
            ->where(['purpose_id' => $purposeId])
            ->latest()
            ->offset($offset)
            ->limit(10)
            ->get()
            ->sortBy('id')
            ->values()
            ->all();

        $data['offset'] = (int) $offset;
        $message = "More Conversion Data Shown Successfull";
        return $this->responseSuccess(200, true, $message, $data);
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
