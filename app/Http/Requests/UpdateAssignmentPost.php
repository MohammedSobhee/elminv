<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAssignmentPost extends FormRequest {
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
            'id' => 'required',
            'points' => 'integer|nullable',
            'category_id' => 'integer',
            'status' => 'integer'

        ];
    }

    public function messages() {
        return [
            'id.required' => 'An ID is required.',
            'points.integer' => 'Points must be an integer',
            'category_id.integer' => 'category_id must be an integer',
            'status.integer' => 'status must be an integer'
        ];
    }
}
