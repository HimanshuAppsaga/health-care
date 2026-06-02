<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CheckTokenRequest extends FormRequest
{
    public function rules()
    {
        return [
            'appointment_id' => 'required|exists:appointments,id',
        ];
    }
}
