<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CaseRequest extends FormRequest
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
            'service_id' => 'required',
            'citizen_id' => 'required',
            'consultant_id' => 'required',
            'title' => 'required|string|max:50',
            'description' => 'nullable|string',
            'status' => 'required',
            'file' => 'nullable',
            'case_initial_date' => 'required',
            'case_status_date' => 'nullable',
            'consultant_review_comment' => 'string|max:255',
            'citizen_review_comment' => 'string|max:255',
            'case_code' => 'string|required',
        ];

        return $rules;
    }
}
