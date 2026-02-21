<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWhatsAppCrmOutgoingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::in(['chat'])],
            'user' => ['required', 'string', 'max:255'],
            'instance_key' => ['nullable', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:40'],
            'content' => ['required', 'string', 'max:65000'],
            'APIKEY' => ['required', 'string', 'max:255'],
            'crm_user_id' => ['nullable', 'integer', 'min:1'],
            'crm_customer_id' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => 'El id del mensaje es obligatorio.',
            'type.in' => 'El tipo de mensaje no es soportado para este canal.',
            'phone.required' => 'El telefono destino es obligatorio.',
            'content.required' => 'El contenido del mensaje es obligatorio.',
            'APIKEY.required' => 'El API KEY es obligatorio.',
        ];
    }
}
