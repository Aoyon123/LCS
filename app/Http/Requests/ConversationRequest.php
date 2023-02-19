<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConversationRequest extends FormRequest
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
            'citizen_id' => 'nullable',
            'consultant_id' => 'nullable',
            'case_message' => 'required',
            'case_id' => 'nullable',
            'time' => 'nullable',
            'seen_status' => 'nullable',
            'status' => 'nullable',
            'is_delete' => 'nullable'
        ];

        return $rules;
    }
}
