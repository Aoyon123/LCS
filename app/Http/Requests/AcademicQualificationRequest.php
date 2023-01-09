<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AcademicQualificationRequest extends FormRequest
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
            'education_level' => 'required|string',
            'institute_name' => 'required|string|max:50',
            'passing_year' => 'required|max:10',
            'certification_copy' => 'required',
            'user_id' => 'required|int|exists:users,id|unique:academic_qualifications,user_id,'
        ];

        if (request()->isMethod('put') || request()->isMethod('patch')) {
            $rules['user_id'] = '';
        }

        return $rules;
    }
}
