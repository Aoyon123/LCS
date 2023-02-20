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
            'sender_id' => 'nullable',
            'receiver_id' => 'nullable',
            'message' => 'required',
            'purpose_id' => 'nullable',
            'purpose_type' => 'nullable',
            'time' => 'nullable',
            'seen_status' => 'nullable',
            'status' => 'nullable',
        ];

        return $rules;
    }
}
