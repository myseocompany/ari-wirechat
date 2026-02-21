<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TwilioCallsReportRequest extends FormRequest
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
            'call_sid' => ['nullable', 'string', 'max:191'],
            'customer_search' => ['nullable', 'string', 'max:191'],
            'phone' => ['nullable', 'string', 'max:64'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'twilio_status' => ['nullable', 'string', 'max:64'],
            'has_recording' => ['nullable', 'in:yes,no'],
        ];
    }

    public function messages(): array
    {
        return [
            'to_date.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha inicial.',
        ];
    }
}
