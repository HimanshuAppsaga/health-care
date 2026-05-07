<?php

namespace App\Http\Requests\Api;

use App\Services\TokenTransferService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class TransferTokenRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'doctor_id' => 'required|exists:doctors,id',
        ];
    }

    /**
     * Transfer the token.
     */
    public function transferToken(TokenTransferService $tokenTransferService): array
    {
        $clinic = $this->clinic;
        $doctorId = $this->doctor_id;
        $transferCount = $clinic->transfer_depth ?: 1;

        return $tokenTransferService->transferToken($clinic->id, $doctorId, $transferCount);
    }
}
