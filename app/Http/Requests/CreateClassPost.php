<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateClassPost extends FormRequest {
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
            'class_name' => 'required',
            'class_type' => 'integer'
        ];
    }

    public function messages() {
        return [
            'class_name.required' => 'A class name is required.',
            'class_type.integer' => 'You must select a grade level'
        ];
    }
}
