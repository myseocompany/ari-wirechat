<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OpportunityReportRequest extends FormRequest
{
    /**
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
            'action_note_search' => ['nullable', 'string', 'max:200'],
            'notes_tags' => ['nullable', 'string', 'max:500'],
            'status_ids' => ['nullable', 'array'],
            'status_ids.*' => ['integer', 'exists:customer_statuses,id'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],
            'tag_none' => ['nullable', 'boolean'],
            'source_id' => ['nullable', 'integer', 'exists:customer_sources,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'priority' => ['nullable', 'in:high,medium,low'],
            'maker' => ['nullable', 'in:unknown,project,makes,other'],
            'production_min' => ['nullable', 'integer', 'min:0'],
            'llm' => ['nullable', 'boolean'],
            'llm_only' => ['nullable', 'boolean'],
            'llm_limit' => ['nullable', 'integer', 'min:1', 'max:200'],
            'unattended' => ['nullable', 'boolean'],
            'export' => ['nullable', 'in:csv'],
            'limit' => ['nullable', 'integer', 'min:10', 'max:3000'],
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
