<?php

namespace App\Http\Requests\Voip;

use Illuminate\Foundation\Http\FormRequest;

class PlaceCustomerCallRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'agent_phone' => ['nullable', 'string', 'max:20', 'regex:/^\+?[1-9]\d{6,14}$/'],
            'to' => ['nullable', 'string', 'max:20', 'regex:/^\+?[1-9]\d{6,14}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'agent_phone.max' => 'El teléfono del asesor no puede superar 20 caracteres.',
            'agent_phone.regex' => 'El teléfono del asesor debe estar en formato E.164. Ejemplo: +573001234567.',
            'to.max' => 'El teléfono destino no puede superar 20 caracteres.',
            'to.regex' => 'El teléfono destino debe estar en formato E.164. Ejemplo: +573001234567.',
        ];
    }

    public function agentPhone(): ?string
    {
        $phone = trim((string) $this->validated('agent_phone', ''));

        return $phone === '' ? null : $phone;
    }

    public function destinationNumber(): ?string
    {
        $phone = trim((string) $this->validated('to', ''));

        return $phone === '' ? null : $phone;
    }
}
