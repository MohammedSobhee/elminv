<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateProjectPost extends FormRequest {

    public function authorize() {
        return true;
    }

    public function rules() {
        return [
            'project_name' => 'required'
        ];
    }

    public function messages() {
        return [
            'project_name.required' => 'A project name is required.'
        ];
    }
}
