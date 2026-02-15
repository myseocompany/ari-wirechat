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
            'to' => ['nullable', 'string', 'max:20', 'regex:/^\+?[1-9]\d{6,14}$/'],
            'client' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'to.max' => 'El teléfono destino no puede superar 20 caracteres.',
            'to.regex' => 'El teléfono destino debe estar en formato E.164. Ejemplo: +573001234567.',
        ];
    }

    public function destinationNumber(): ?string
    {
        $phone = trim((string) $this->validated('to', ''));

        return $phone === '' ? null : $phone;
    }

    public function isClientPreparation(): bool
    {
        return (bool) $this->boolean('client');
    }
}
