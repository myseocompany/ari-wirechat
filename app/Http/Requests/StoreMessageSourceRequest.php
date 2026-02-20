<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMessageSourceRequest extends FormRequest
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
            'type' => ['required', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:50'],
            'APIKEY' => ['required', 'string', 'max:255', Rule::unique('message_sources', 'APIKEY')],
            'webhook_url' => ['nullable', 'string', 'max:2048'],
            'source_id' => ['nullable', 'integer', 'min:1'],
            'active' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'El tipo de canal es obligatorio.',
            'APIKEY.required' => 'El API KEY es obligatorio.',
            'APIKEY.unique' => 'Ese API KEY ya está siendo usado por otra línea.',
            'source_id.integer' => 'El source_id debe ser numérico.',
            'source_id.min' => 'El source_id debe ser mayor a cero.',
        ];
    }
}
