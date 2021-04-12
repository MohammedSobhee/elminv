<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ActivationPost extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */

    public function authorize() {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        return [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|unique:users',
            'password' => [
                'required',
                'regex:' . config('constants.password_regex')
            ],
            'password_confirm' => 'required|same:password'
        ];
    }
    public function messages() {
        return [
            'first_name.required' => 'A first name is required.',
            'last_name.required' => 'A last name is required.',
            'email.required' => 'An Email Address is required.',
            'email.unique' => 'The Email Address entered already exists in our system.',
            'password.required' => 'A Password is required.',
            'password.regex' => 'The password must contain least one uppercase letter, a number, and be 6 or more characters.',
            'password_confirm.required' => 'Confirm Password is required.',
            'password_confirm.same' => 'Confirm Password must match Password.'
        ];
    }
}
