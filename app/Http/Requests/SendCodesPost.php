<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendCodesPost extends FormRequest {
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
            'code' => 'required',
            'url' => 'required',
            'email.*' => 'required|email'
        ];
    }
    public function messages() {
        return [
            'code' => 'A code is required.',
            'email.email' => 'You must supply valid email addresses.',
            'url' => 'A url is required.'
        ];
    }
}
