<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConsultantRateRequest extends FormRequest
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
            'citizen_id' => 'required',
            'consultant_id' => 'required',
            'rate' => 'required|lte:5.0|gte:0.0',
            'against_id' => 'required',
        ];

        return $rules;
    }
}
