<?php

namespace App\Http\Requests\Voip;

use Illuminate\Foundation\Http\FormRequest;

class PlaceCallRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'to' => ['required', 'string', 'max:20', 'regex:/^\+?[1-9]\d{6,14}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'to.required' => 'Debes indicar el número destino.',
            'to.max' => 'El destino no puede superar 20 caracteres.',
            'to.regex' => 'El destino debe estar en formato E.164. Ejemplo: +573001234567.',
        ];
    }

    public function destinationNumber(): string
    {
        return trim((string) $this->validated('to'));
    }
}
