<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchChannelsCallsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'call_id' => ['nullable', 'string', 'max:191'],
            'agent_id' => ['nullable', 'string', 'max:191'],
            'msisdn' => ['nullable', 'string', 'max:64'],
            'only_missing' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'to_date.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha inicial.',
        ];
    }
}
