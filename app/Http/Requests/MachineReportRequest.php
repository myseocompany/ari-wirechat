<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MachineReportRequest extends FormRequest
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
            'serial' => ['nullable', 'string', 'max:80'],
            'batch_id' => ['nullable', 'string', 'max:64'],
            'reported_at' => ['nullable', 'date'],
            'reports' => ['required', 'array', 'min:1'],
            'reports.*.minute_at' => ['required', 'date'],
            'reports.*.tacometer_total' => ['required', 'integer', 'min:0'],
            'reports.*.units_in_minute' => ['required', 'integer'],
            'reports.*.is_backfill' => ['sometimes', 'boolean'],
            'reports.*.faults' => ['sometimes', 'array'],
            'reports.*.faults.*.code' => ['required_with:reports.*.faults', 'string', 'max:80'],
            'reports.*.faults.*.severity' => ['required_with:reports.*.faults', 'string', 'max:20'],
            'reports.*.faults.*.reported_at' => ['sometimes', 'date'],
            'reports.*.faults.*.metadata' => ['sometimes', 'array'],
            'faults' => ['sometimes', 'array'],
            'faults.*.code' => ['required_with:faults', 'string', 'max:80'],
            'faults.*.severity' => ['required_with:faults', 'string', 'max:20'],
            'faults.*.reported_at' => ['sometimes', 'date'],
            'faults.*.metadata' => ['sometimes', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'reports.required' => 'At least one report is required.',
            'reports.*.minute_at.required' => 'Each report must include minute_at.',
            'reports.*.tacometer_total.required' => 'Each report must include tacometer_total.',
            'reports.*.units_in_minute.required' => 'Each report must include units_in_minute.',
            'reports.*.faults.*.code.required_with' => 'Each fault must include a code.',
            'reports.*.faults.*.severity.required_with' => 'Each fault must include a severity.',
            'faults.*.code.required_with' => 'Each fault must include a code.',
            'faults.*.severity.required_with' => 'Each fault must include a severity.',
        ];
    }
}
