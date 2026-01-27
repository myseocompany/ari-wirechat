<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LeadConversationClassificationReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
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
            'status' => ['nullable', 'string', 'in:calificado,nurturing,no_calificado'],
            'score_min' => ['nullable', 'integer', 'min:0', 'max:100'],
            'score_max' => ['nullable', 'integer', 'min:0', 'max:100', 'gte:score_min'],
            'classifier_version' => ['nullable', 'string', 'max:32'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'conversation_id' => ['nullable', 'integer'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'user_unassigned' => ['nullable', 'boolean'],
            'status_ids' => ['nullable', 'array'],
            'status_ids.*' => ['integer', 'exists:customer_statuses,id'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],
            'tag_none' => ['nullable', 'boolean'],
            'suggested_tag_ids' => ['nullable', 'array'],
            'suggested_tag_ids.*' => ['integer', 'exists:tags,id'],
            'applied_tag_ids' => ['nullable', 'array'],
            'applied_tag_ids.*' => ['integer', 'exists:tags,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'to_date.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha inicial.',
            'score_max.gte' => 'El score máximo debe ser mayor o igual al score mínimo.',
        ];
    }
}
