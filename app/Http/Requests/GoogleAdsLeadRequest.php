<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GoogleAdsLeadRequest extends FormRequest
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
            'lead_id' => ['required', 'string', 'max:255'],
            'user_column_data' => ['required', 'array'],
            'user_column_data.*.column_id' => ['required', 'string', 'max:255'],
            'user_column_data.*.string_value' => ['nullable', 'string', 'max:2000'],
            'user_column_data.*.column_name' => ['nullable', 'string', 'max:255'],
            'api_version' => ['nullable', 'string', 'max:50'],
            'form_id' => ['nullable', 'integer'],
            'campaign_id' => ['nullable', 'integer'],
            'adgroup_id' => ['nullable', 'integer'],
            'creative_id' => ['nullable', 'integer'],
            'google_key' => ['nullable', 'string', 'max:255'],
            'gcl_id' => ['nullable', 'string', 'max:255'],
            'is_test' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'lead_id.required' => 'El lead_id es obligatorio.',
            'user_column_data.required' => 'El listado de campos del formulario es obligatorio.',
            'user_column_data.array' => 'El listado de campos del formulario debe ser un arreglo.',
            'user_column_data.*.column_id.required' => 'Cada campo debe incluir column_id.',
            'user_column_data.*.column_id.string' => 'Cada column_id debe ser texto.',
            'user_column_data.*.string_value.string' => 'Cada valor debe ser texto.',
            'form_id.integer' => 'El form_id debe ser numérico.',
            'campaign_id.integer' => 'El campaign_id debe ser numérico.',
            'adgroup_id.integer' => 'El adgroup_id debe ser numérico.',
            'creative_id.integer' => 'El creative_id debe ser numérico.',
            'is_test.boolean' => 'El campo is_test debe ser verdadero o falso.',
        ];
    }
}
