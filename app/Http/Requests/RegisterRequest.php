<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
        // $id = request()->route('id') ?? null;

        $rules = [
            'name' => 'required|string|max:50',
            'phone' => 'max:11|min:11|regex:/(01)[0-9]{9}/|unique:users',
            'password' => 'required|min:8',
            'email'    => 'email|unique:users,email',
            // "nid" => 'required',
            // "dob" => 'required',
            "profile_image" => 'nullable|image|mimes:jpeg,png,jpg,gif|max:1024',

        ];

        if (request()->isMethod('put') || request()->isMethod('patch')) {
            $rules['password'] = 'nullable|min:8';
        }

        return $rules;
    }
}
