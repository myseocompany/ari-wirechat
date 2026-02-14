<?php

namespace App\Http\Requests\Voip;

use Illuminate\Foundation\Http\FormRequest;

class GenerateTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'identity' => ['nullable', 'string', 'max:80', 'regex:/^[A-Za-z0-9._-]+$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'identity.max' => 'La identidad no puede superar 80 caracteres.',
            'identity.regex' => 'La identidad solo permite letras, números, punto, guion y guion bajo.',
        ];
    }

    public function resolvedIdentity(): string
    {
        $identity = trim((string) $this->input('identity'));

        if ($identity !== '') {
            return $identity;
        }

        $authId = (string) ($this->user()?->getAuthIdentifier() ?? 'guest');

        return 'user-'.$authId;
    }
}
