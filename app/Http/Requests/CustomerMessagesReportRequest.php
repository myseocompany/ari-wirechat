<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerMessagesReportRequest extends FormRequest
{
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
            'messages_min' => ['nullable', 'integer', 'min:0'],
            'messages_max' => ['nullable', 'integer', 'min:0', 'gte:messages_min'],
            'message_search' => ['nullable', 'string', 'max:200'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'user_unassigned' => ['nullable', 'boolean'],
            'status_ids' => ['nullable', 'array'],
            'status_ids.*' => ['integer', 'exists:customer_statuses,id'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],
            'tag_none' => ['nullable', 'boolean'],
            'without_actions_last_30_days' => ['nullable', 'boolean'],
            'without_actions_last_60_days' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'to_date.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha inicial.',
            'messages_max.gte' => 'El maximo de mensajes debe ser mayor o igual al minimo.',
        ];
    }
}
