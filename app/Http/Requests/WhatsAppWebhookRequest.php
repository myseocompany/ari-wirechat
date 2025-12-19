<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WhatsAppWebhookRequest extends FormRequest
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
            'entry' => ['sometimes', 'array'],
            'entry.*.changes' => ['sometimes', 'array'],
            'entry.*.changes.*.value' => ['sometimes', 'array'],
            'entry.*.changes.*.value.messages' => ['sometimes', 'array'],
            'entry.*.changes.*.value.messages.*.id' => ['sometimes', 'string'],
            'entry.*.changes.*.value.messages.*.from' => ['sometimes', 'string'],
            'entry.*.changes.*.value.messages.*.timestamp' => ['sometimes', 'string'],
            'entry.*.changes.*.value.messages.*.type' => ['sometimes', 'string'],
        ];
    }
}
