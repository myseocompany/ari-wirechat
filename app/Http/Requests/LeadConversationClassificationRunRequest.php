<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LeadConversationClassificationRunRequest extends FormRequest
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
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'limit' => ['required', 'integer', 'in:0,5,10,50'],
            'classifier_version' => ['nullable', 'string', 'max:32'],
        ];
    }

    public function messages(): array
    {
        return [
            'to_date.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha inicial.',
            'limit.in' => 'El limite debe ser 0 (todas), 5, 10 o 50.',
        ];
    }
}
