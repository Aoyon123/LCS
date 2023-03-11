<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FrequentlyAskedQuestionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $rules = [
            'category_name' => 'nullable|string',
            'question' => 'nullable|string',
            'answer' => 'nullable|string',
            'answer_image' => 'nullable|string',
            'status' => 'nullable',
        ];

        return $rules;
    }
}
