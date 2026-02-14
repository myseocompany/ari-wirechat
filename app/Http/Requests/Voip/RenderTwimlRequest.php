<?php

namespace App\Http\Requests\Voip;

use Illuminate\Foundation\Http\FormRequest;

class RenderTwimlRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'To' => ['nullable', 'string', 'max:20', 'regex:/^\+?[1-9]\d{6,14}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'To.max' => 'El destino no puede superar 20 caracteres.',
            'To.regex' => 'El destino debe estar en formato E.164. Ejemplo: +573001234567.',
        ];
    }

    public function destinationNumber(): ?string
    {
        $to = trim((string) $this->validated('To', ''));

        if ($to === '') {
            return null;
        }

        return $to;
    }
}
