<?php

namespace App\Http\Requests\Voip;

use Illuminate\Foundation\Http\FormRequest;

class TwilioStatusCallbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action_id' => ['nullable', 'integer'],
            'token' => ['nullable', 'string', 'max:255'],
            'AccountSid' => ['required', 'string', 'max:64'],
            'CallSid' => ['nullable', 'string', 'regex:/^CA[a-fA-F0-9]{32}$/'],
            'CallStatus' => ['nullable', 'string', 'max:32'],
            'CallDuration' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'AccountSid.required' => 'El callback de Twilio no incluye AccountSid.',
            'CallSid.regex' => 'El CallSid recibido no tiene el formato esperado.',
        ];
    }

    public function actionId(): ?int
    {
        $value = $this->validated('action_id');

        if ($value === null) {
            return null;
        }

        return (int) $value;
    }

    public function webhookToken(): ?string
    {
        $value = trim((string) $this->validated('token', ''));

        return $value === '' ? null : $value;
    }

    public function accountSid(): string
    {
        return trim((string) $this->validated('AccountSid'));
    }

    public function callSid(): ?string
    {
        $value = trim((string) $this->validated('CallSid', ''));

        return $value === '' ? null : $value;
    }

    public function callStatus(): ?string
    {
        $value = trim((string) $this->validated('CallStatus', ''));

        return $value === '' ? null : $value;
    }

    public function callDuration(): ?int
    {
        $value = $this->validated('CallDuration');

        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }
}
