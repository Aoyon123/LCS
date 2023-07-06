<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Helper\FileHandler;
use App\Http\Requests\FrequentlyAskedQuestionRequest;
use App\Models\FrequentlyAskedQuestion;
use App\Traits\ResponseTrait;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class FrequentlyAskedQuestionController extends Controller
{
    use ResponseTrait;
    public function index()
    {
        $faqData = FrequentlyAskedQuestion::activefrequentlyaskedquestion()->orderBy('frequently_asked_questions.id', 'desc')->get();
        $groupFaqData = $faqData->groupBy('category_name');

        if ($groupFaqData) {
            $message = "Succesfully Data Shown";
            return $this->responseSuccess(200, true, $message, $groupFaqData);
        } else {
            $message = "Invalid credentials";
            return $this->responseError(403, false, $message);
        }
    }

    public function retrieve($id)
    {
        DB::beginTransaction();
        try {
            $data = FrequentlyAskedQuestion::findOrFail($id);
            if ($data) {
                $message = "FAQ Found";
                DB::commit();
                return $this->responseSuccess(200, true, $message, $data);
            } else {
                $message = "No Data Found";
                return $this->responseError(404, false, $message);
            }
        } catch (QueryException $e) {
            DB::rollBack();
        }
    }

    public function store(FrequentlyAskedQuestionRequest $request)
    {
        DB::beginTransaction();
        try {
            if ($request->answer_image) {
                $image_parts = explode(";base64,", $request->answer_image);
                $unique = random_int(100000, 999999);
                $imageType = explode("/", $image_parts[0])[1];
                $type = "faq-icon";
                if (isset($image_parts[1])) {
                    $answer_image_path = FileHandler::uploadImage($request->answer_image, $type, $unique, $imageType, 'faqAnswer');
                }
            }

            $data = FrequentlyAskedQuestion::create([
                'category_name' => $request->category_name,
                'question' => $request->question,
                'answer' => $request->answer,
                'answer_image' => $answer_image_path ?? '',
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
    public function update(Request $request, $id)
    {
        $faqData = FrequentlyAskedQuestion::findOrFail($id);
        // return $request->answer_image;
        // if ($request->answer_image) {
        //     $image_parts = explode(";base64,", $request->answer_image);
        //     $imageType = explode("/", $image_parts[0])[1];
        //     if (isset($image_parts[1])) {
        //         $answer_image_path = FileHandler::uploadImage($request->answer_image, $faqData->category_name, $faqData->id, $imageType, 'faqAnswer');
        //       //  return $answer_image_path;
        //         if (File::exists($answer_image_path)) {
        //             File::delete($answer_image_path);
        //         }
        //     }
        //   }
        //  else {
        //         $answer_image_path = $faqData->answer_image;
        //     }

        if ($request->answer_image) {

            $image_parts = explode(";base64,", $request->answer_image);

            $imageType = explode("/", $image_parts[0])[1];
            $unique = random_int(100000, 999999);
            $type = "faq-icon";

            if (isset($image_parts[1])) {
                $answer_image_path = FileHandler::uploadImage($request->answer_image, $type, $unique, $imageType, 'service');

                if (File::exists(public_path($faqData->answer_image))) {
                    File::delete(public_path($faqData->answer_image));
                }
            } else {
                $answer_image_path = $faqData->answer_image;
            }
        } else {
            $answer_image_path = $faqData->answer_image;
        }

        if ($faqData) {
            $faqData->update([
                'category_name' => $request->category_name ?? $faqData->category_name,
                'question' => $request->question ?? $faqData->question,
                'answer' => $request->answer ?? $faqData->answer,
                'answer_image' => $answer_image_path,
                'status' => $request->status ?? $faqData->status,
            ]);

            $message = "FAQ Updated Succesfully";
            DB::commit();
            return $this->responseSuccess(200, true, $message, $faqData);
        } else {
            $message = "No Data Found";
            return $this->responseError(404, false, $message);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $faqData = FrequentlyAskedQuestion::findOrFail($id);
            if ($faqData) {
                $faqData->delete();
                $message = "FAQ Deleted Succesfully";
                DB::commit();
                return $this->responseSuccess(200, true, $message, []);
            }
        } catch (QueryException $e) {
            DB::rollBack();
        }
    }
}
