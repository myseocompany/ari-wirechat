<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QueueTwilioRecoveriesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'selected_indexes' => ['required', 'array', 'min:1'],
            'selected_indexes.*' => ['integer', 'min:0'],
            'calls' => ['required', 'array'],
            'calls.*.call_sid' => ['required', 'string', 'max:191'],
            'calls.*.call_created_at' => ['nullable', 'date'],
            'calls.*.from_number' => ['nullable', 'string', 'max:64'],
            'calls.*.to_number' => ['nullable', 'string', 'max:64'],
            'calls.*.contact_msisdn' => ['nullable', 'string', 'max:64'],
            'calls.*.direction' => ['nullable', 'string', 'max:64'],
            'calls.*.status_text' => ['nullable', 'string', 'max:64'],
            'calls.*.duration_seconds' => ['nullable', 'integer', 'min:0'],
            'calls.*.recording_exists' => ['nullable', 'boolean'],
            'calls.*.recording_sid' => ['nullable', 'string', 'max:64'],
            'calls.*.recording_url' => ['nullable', 'string', 'max:2048'],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date'],
            'call_sid' => ['nullable', 'string', 'max:191'],
            'msisdn' => ['nullable', 'string', 'max:64'],
            'status' => ['nullable', 'string', 'max:64'],
            'only_missing' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'selected_indexes.required' => 'Selecciona al menos una llamada faltante.',
            'selected_indexes.min' => 'Selecciona al menos una llamada faltante.',
        ];
    }
}
