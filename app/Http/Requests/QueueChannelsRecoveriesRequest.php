<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QueueChannelsRecoveriesRequest extends FormRequest
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
            'calls.*.call_id' => ['required', 'string', 'max:191'],
            'calls.*.call_created_at' => ['nullable', 'date'],
            'calls.*.msisdn' => ['nullable', 'string', 'max:64'],
            'calls.*.agent_id' => ['nullable', 'string', 'max:64'],
            'calls.*.agent_username' => ['nullable', 'string', 'max:191'],
            'calls.*.agent_name' => ['nullable', 'string', 'max:120'],
            'calls.*.agent_surname' => ['nullable', 'string', 'max:120'],
            'calls.*.agent_msisdn' => ['nullable', 'string', 'max:64'],
            'calls.*.call_duration_seconds' => ['nullable', 'integer', 'min:0'],
            'calls.*.recording_exists' => ['nullable', 'boolean'],
            'calls.*.recording_url' => ['nullable', 'string', 'max:2048'],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date'],
            'call_id' => ['nullable', 'string', 'max:191'],
            'agent_id' => ['nullable', 'string', 'max:191'],
            'msisdn' => ['nullable', 'string', 'max:64'],
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
