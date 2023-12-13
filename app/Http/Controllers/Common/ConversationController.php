<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Http\Helper\FileHandler;
use App\Http\Helper\SendNotification;
use App\Models\Conversation;
use App\Models\LcsCase;
use App\Models\User;
use App\Traits\ResponseTrait;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

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
        $message = "See More Conversion Data Shown Successfull";
        return $this->responseSuccess(200, true, $message, $data);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {

            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $filenameWithExt = $file->getClientOriginalName();
                $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $fileNameToStore = $filename . '_' . time() . '.' . $extension;

                $relativePath = '/uploads/conversationAttachment/' . $fileNameToStore;

                $file->move(public_path('/uploads/conversationAttachment'), $fileNameToStore);
                $attachmentConversation = $relativePath;

                if ($extension != 'pdf') {
                    FileHandler::imageOptimize(public_path($attachmentConversation));
                }
            } else {
                $attachmentConversation = null;
            }

            // for the you are not allowed to send message for this case
            // $currentTime = Carbon::now()->toDateTimeString();
            // $caseData = LcsCase::findOrFail($request->purpose_id);
            // $type = auth()->user()->type;
            // if ($currentTime > $caseData->case_complete_date && $type == 'citizen') {
            //     $message = "Dear VumiSeba Customer, Your issue has been resolved by the consultant, please apply again for re-service.";
            //     return $this->responseError(404, false, $message);
            // }

            // for conversation creation
            $data = Conversation::create([
                'sender_id' => auth()->user()->id,
                'receiver_id' => $request->receiver_id,
                'message' => $request->message ?? '',
                'purpose_id' => $request->purpose_id,
                'purpose_type' => $request->purpose_type,
                'time' => Carbon::now()->toDateTimeString(),
                'status' => 0,
                'seen_status' => 0,
                'attachment' => $attachmentConversation,
            ]);

            $data = Conversation::with(['sender:id,name,profile_image', 'receiver:id,name,profile_image'])
                ->where(['id' => $data->id])
                ->first();

            $deviceToken = ['device_token' => User::where('id', $request->receiver_id)->first()->device_token];

            // Check if status and device token are present in the request
            if (!empty($request->message) && $deviceToken['device_token'] != null) {
                // Create a new instance of the SendNotification class
                $sendNotification = new SendNotification();
                // Set the FCM token, title, and body based on the status
                $FcmToken = [$deviceToken['device_token']];

                // Here receiver name in the title
                $title = $data->sender->name;

                $body = $request->message;
                $message = "Conversation Created Successfull";
                if ($this->responseSuccess(200, true, $message, $data)) {
                    $sendNotification->sendNotification($FcmToken, $title, $body);
                }
            }

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
        try {
            $conversationData = Conversation::findOrFail($id);
            if ($conversationData) {
                $conversationData->delete();
            }
            $message = "Conversation Data Deleted Successfully";

            return $this->responseSuccess(200, true, $message, []);
        } catch (QueryException $e) {
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, $e->getMessage());
        }
    }

    public function conversation_update($id, Request $request)
    {
        try {
            $conversationData = Conversation::findOrFail($id);

            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $filenameWithExt = $file->getClientOriginalName();
                $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $fileNameToStore = $filename . '_' . time() . '.' . $extension;

                $relativePath = '/uploads/conversationAttachment/' . $fileNameToStore;

                $file->move(public_path('/uploads/conversationAttachment'), $fileNameToStore);
                $attachmentConversation = $relativePath;

                if ($extension != 'pdf') {
                    FileHandler::imageOptimize(public_path($attachmentConversation));
                }
            } else {
                $attachmentConversation = null;
            }

            if ($conversationData) {
                // Validate the request
                $request->validate([
                    'message' => 'string',
                ]);

                // Update the message
                $conversationData->update([
                    'message' => $request->input('message'),
                    'attachment' => $attachmentConversation,
                ]);
            }
            $message = "Conversation Data Updated Successfully";

            return $this->responseSuccess(200, true, $message, $conversationData);
        } catch (QueryException $e) {
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, $e->getMessage());
        }
    }
    public function allMessageMobile(Request $request, $purposeId)
    {

        $this->seenMessage($purposeId);
        $params = $request->all();
        $totalConversation = Conversation::where(['purpose_id' => $purposeId])
            ->get()
            ->sortBy('id')
            ->values()
            ->count();

        $data = [];
        $conversationData = Conversation::with(
            ['sender:id,name,profile_image', 'receiver:id,name,profile_image']
        )
            ->where(['purpose_id' => $purposeId]);

        if (isset($params['limit'])) {
            if (isset($params['offset'])) {
                $data['total'] = $totalConversation;
                $data['offset'] = $params['offset'];
                $data['limit'] = $params['limit'];
                $data['list'] = $conversationData->offset($params['offset'])->limit($params['limit'])->get();
            } else {
                $data['limit'] = $params['limit'];
                $data['list'] = $conversationData->limit($params['limit'])->get();
            }
        } else {
            $data['totalConsultant'] = $totalConversation;
            $data['list'] = $conversationData->get();
        }

        $currentTime = Carbon::now()->toDateTimeString();
        $caseData = LcsCase::findOrFail($purposeId);
        $type = auth()->user()->type;
        if ($currentTime > $caseData->case_complete_date && $caseData->status == 2 && $type == 'citizen') {
            $timeOverStatus = 1;
            $message = "Dear VumiSeba Customer, Your issue has been resolved by the consultant, please apply again for re-service.";
        } else {
            $timeOverStatus = 0;
            $message = "Conversion Data Shown Successfull";
        }

        // return $this->responseSuccess(200, true, $message, $data);
        return response()->json([
            'status_code' => 200,
            'status' => true,
            'timeover' => $timeOverStatus,
            'message' => $message,
            'data' => $data,
        ]);
    }

    public function chatBoardMessage()
    {
        $conversationData = Conversation::with('sender:id,name,profile_image')
            ->select('id', 'sender_id', 'receiver_id', 'purpose_id', 'message', 'attachment')
            ->where(['receiver_id' => auth()->user()->id, 'seen_status' => 0])
            ->groupBy('purpose_id')
            ->latest()
            ->get();

        $message = "Chat Board Message Seen Successfully";
        return $this->responseSuccess(200, true, $message, $conversationData);
    }

    public function chatBoardMessageCount()
    {
        $chatBoardMessageCount = Conversation::where(['receiver_id' => auth()->user()->id, 'seen_status' => 0])
            ->distinct('purpose_id')->count('purpose_id');

        $message = "Chat Board Message Count Shown Successfully";
        return $this->responseSuccess(200, true, $message, $chatBoardMessageCount);
    }

    public function caseDetailsNewMessageCount($caseId)
    {
        $caseDetailsNewMessageCount = Conversation::where('purpose_id', $caseId)
            ->where(['receiver_id' => auth()->user()->id, 'seen_status' => 0])
        // ->distinct('purpose_id')
            ->count();

        $message = "Case Details New Message Count Shown Successfully";
        return $this->responseSuccess(200, true, $message, $caseDetailsNewMessageCount);
    }
}
