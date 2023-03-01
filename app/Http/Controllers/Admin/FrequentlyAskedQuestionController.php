<?php

namespace App\Http\Controllers\Admin;

use App\Traits\ResponseTrait;
use Illuminate\Http\Response;
use App\Http\Requests\FrequentlyAskedQuestionRequest;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use App\Models\FrequentlyAskedQuestion;
use Illuminate\Database\QueryException;


class FrequentlyAskedQuestionController extends Controller
{
    use ResponseTrait;
    public function index()
    {
        $data = FrequentlyAskedQuestion::activefrequentlyaskedquestion()->get();
        // return $data;
        if ($data) {
            $message = "Succesfully Data Shown";
            return $this->responseSuccess(200, true, $message, $data);
        } else {
            $message = "Invalid credentials";
            return $this->responseError(403, false, $message);
        }
    }

    public function store(FrequentlyAskedQuestionRequest $request)
    {
        DB::beginTransaction();
        try {

            $data = FrequentlyAskedQuestion::create([
                'category_name' => $request->category_name,
                'question' => $request->question,
                'answer' => $request->answer,
                'status' => $request->status,
            ]);

            DB::commit();
            $message = "FAQ Created Successfull";
            return $this->responseSuccess(200, true, $message, $data);

        } catch (QueryException $e) {
            DB::rollBack();
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, $e->getMessage());
        }
    }
    public function update(FrequentlyAskedQuestionRequest $request, $id)
    {

        $input = FrequentlyAskedQuestion::findOrFail($id);
        DB::beginTransaction();
        try {
            if ($input) {
                $input->category_name = $request['category_name'];
                $input->question = $request['question'];
                $input->answer = $request['answer'];
                $input->status = $request['status'];
                $input->save();
                $message = "FAQ Updated Succesfully";
                DB::commit();
                return $this->responseSuccess(200, true, $message, $input);
            } else {
                $message = "No Data Found";
                return $this->responseError(404, false, $message);
            }
        } catch (QueryException $e) {
            DB::rollBack();
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $user = FrequentlyAskedQuestion::findOrFail($id);
            if ($user) {
                $user->delete();
                $message = "FAQ Deleted Succesfully";
                DB::commit();
                return $this->responseSuccess(200, true, $message, []);
            }
        } catch (QueryException $e) {
            DB::rollBack();
        }
    }
}
