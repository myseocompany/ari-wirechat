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
            'source' => ['nullable', 'string', 'max:32'],
            'token' => ['nullable', 'string', 'max:255'],
            'AccountSid' => ['nullable', 'string', 'max:64'],
            'To' => ['nullable', 'string', 'max:20', 'regex:/^\+?[1-9]\d{6,14}$/'],
            'Called' => ['nullable', 'string', 'max:20', 'regex:/^\+?[1-9]\d{6,14}$/'],
            'CallSid' => ['nullable', 'string', 'regex:/^CA[a-fA-F0-9]{32}$/'],
            'DialCallSid' => ['nullable', 'string', 'regex:/^CA[a-fA-F0-9]{32}$/'],
            'CallStatus' => ['nullable', 'string', 'max:32'],
            'DialCallStatus' => ['nullable', 'string', 'max:32'],
            'CallDuration' => ['nullable', 'integer', 'min:0'],
            'DialCallDuration' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
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

    public function accountSid(): ?string
    {
        $value = trim((string) $this->validated('AccountSid', ''));

        return $value === '' ? null : $value;
    }

    public function callSid(): ?string
    {
        $value = trim((string) $this->validated('DialCallSid', ''));
        if ($value === '') {
            $value = trim((string) $this->validated('CallSid', ''));
        }

        return $value === '' ? null : $value;
    }

    public function callStatus(): ?string
    {
        $value = trim((string) $this->validated('DialCallStatus', ''));
        if ($value === '') {
            $value = trim((string) $this->validated('CallStatus', ''));
        }

        return $value === '' ? null : $value;
    }

    public function callDuration(): ?int
    {
        $value = $this->validated('DialCallDuration');
        if ($value === null || $value === '') {
            $value = $this->validated('CallDuration');
        }

        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    public function destinationNumber(): ?string
    {
        $value = trim((string) $this->validated('To', ''));
        if ($value === '') {
            $value = trim((string) $this->validated('Called', ''));
        }

        return $value === '' ? null : $value;
    }
}
