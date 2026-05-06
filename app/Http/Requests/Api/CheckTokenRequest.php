<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CheckTokenRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string',
            'phone' => 'required|string',
        ];
    }
}