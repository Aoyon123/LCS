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
            'service_id' => 'nullable',
            'citizen_id' => 'nullable',
            'consultant_id' => 'nullable',
            'title' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'status' => 'nullable',
            'file' => 'nullable',
            'case_initial_date' => 'nullable',
            'case_status_date' => 'nullable',
            'consultant_review_comment' => 'string|nullable|max:255',
            'citizen_review_comment' => 'nullable|string|max:255',
            'case_code' => 'string|nullable',
        ];

        return $rules;
    }
}
