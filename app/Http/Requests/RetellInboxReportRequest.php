<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RetellInboxReportRequest extends FormRequest
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
            'call_id' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:255'],
            'event' => ['nullable', 'string', 'max:100'],
            'payload_search' => ['nullable', 'string', 'max:255'],
            'process_state' => ['nullable', 'in:all,pending,processed,error'],
            'has_action' => ['nullable', 'boolean'],
            'call_successful' => ['nullable', 'in:all,yes,no,unknown'],
            'in_voicemail' => ['nullable', 'in:all,yes,no,unknown'],
            'busca_automatizar' => ['nullable', 'in:all,yes,no,unknown'],
            'masses_used' => ['nullable', 'string', 'max:255'],
            'daily_volume_min' => ['nullable', 'integer', 'min:0'],
            'daily_volume_max' => ['nullable', 'integer', 'min:0', 'gte:daily_volume_min'],
            'live_attendance_status' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'to_date.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha inicial.',
            'daily_volume_max.gte' => 'El volumen máximo debe ser mayor o igual al volumen mínimo.',
        ];
    }
}
