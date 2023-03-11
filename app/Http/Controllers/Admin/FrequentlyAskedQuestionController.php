<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use Illuminate\Http\Response;
use App\Http\Helper\FileHandler;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use App\Models\FrequentlyAskedQuestion;
use Illuminate\Database\QueryException;
use App\Http\Requests\FrequentlyAskedQuestionRequest;


class FrequentlyAskedQuestionController extends Controller
{
    use ResponseTrait;
    public function index()
    {
        $faqData = FrequentlyAskedQuestion::activefrequentlyaskedquestion()->get();
        $groupFaqData = $faqData->groupBy('category_name');

        if ($groupFaqData) {
            $message = "Succesfully Data Shown";
            return $this->responseSuccess(200, true, $message, $groupFaqData);
        } else {
            $message = "Invalid credentials";
            return $this->responseError(403, false, $message);
        }
    }

    public function store(FrequentlyAskedQuestionRequest $request)
    {
        DB::beginTransaction();
        try {
            //  return $request->id;
            if ($request->answer_image) {
                $category_name = str_replace(' ', '', $request->category_name);
                $image_parts = explode(";base64,", $request->answer_image);
                if (isset($image_parts[0])) {
                    $answer_image_path = FileHandler::uploadfaqImage($request->answer_image, $category_name, $request->id, 'faqAnswer');
                }
            }

            $data = FrequentlyAskedQuestion::create([
                'category_name' => $request->category_name,
                'question' => $request->question,
                'answer' => $request->answer,
                'answer_image' => $answer_image_path ?? null,
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
        // return $request->question;
        $faqData = FrequentlyAskedQuestion::findOrFail($id);
        //   return $faqData;
        // return $faqData->category_name;
        DB::beginTransaction();
        try {
            if ($request->answer_image) {
                $image_parts = explode(";base64,", $request->answer_image);
                if (isset($image_parts[0])) {
                    $answer_image_path = FileHandler::uploadfaqImage($request->answer_image, $faqData->category_name, $request->id, 'faqAnswer');
                    if (File::exists($answer_image_path)) {
                        File::delete($answer_image_path);
                    }
                } else {
                    $answer_image_path = $faqData->answer_image;
                }
            }

            if ($faqData) {
                $faqData->update([
                    'category_name' => $request->category_name ?? $faqData->category_name,
                    'question' => $request->question ?? $faqData->question,
                    'answer' => $request->answer ?? $faqData->answer,
                    'answer_image' => $request->answer_image_path ?? $faqData->answer_image,
                    'status' => $request->status ?? $faqData->status,
                ]);

                $message = "FAQ Updated Succesfully";
                DB::commit();
                return $this->responseSuccess(200, true, $message, $faqData);
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
