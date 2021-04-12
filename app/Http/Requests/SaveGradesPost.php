<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveGradesPost extends FormRequest {
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
            'type' => 'required',
            'type_id' => 'required',
            'category_id' => 'required',
            'user_id' => 'required',
            'points' => 'integer|nullable',
            'teacher_id' => 'nullable'
        ];
    }
}
