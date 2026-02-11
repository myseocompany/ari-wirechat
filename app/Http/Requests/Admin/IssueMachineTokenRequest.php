<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class IssueMachineTokenRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'confirm_issue' => ['required', 'accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'confirm_issue.required' => 'Confirma la emisión del token.',
            'confirm_issue.accepted' => 'Debes confirmar la emisión del token.',
        ];
    }
}
