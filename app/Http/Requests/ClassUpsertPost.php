<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClassUpsertPost extends FormRequest {
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
            'class_id' => 'required',
            'assignment_id' => 'required|integer',
            'status' => 'required|integer'
        ];
    }

    public function messages() {
        return [
            'class_id.required' => 'Class_id is required.',
            'status.required' => 'Status is required.',
            'status.integer' => 'Status must be an integer.'
        ];
    }
}
