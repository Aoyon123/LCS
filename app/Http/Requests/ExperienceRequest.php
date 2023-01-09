<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExperienceRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        $rules = [
            'Institute_name' => 'required|string|max:30',
            'designation' => 'required|max:30',
            'department' => 'required|max:30',
            'start_date' => 'required|max:30',
            'end_date' => 'required|max:50',
            'current_working' => 'nullable',
            'user_id' => 'required|int|exists:users,id|unique:experiences,user_id,'
        ];
        if (request()->isMethod('put') || request()->isMethod('patch')) {
            $rules['user_id'] = '';
        }
        return $rules;
    }
}
