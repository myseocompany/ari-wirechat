<?php

namespace App\Http\Requests\Voip;

use Illuminate\Foundation\Http\FormRequest;

class TwilioRecordingCallbackRequest extends FormRequest
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
            'AccountSid' => ['nullable', 'string', 'max:64'],
            'To' => ['nullable', 'string', 'max:20', 'regex:/^\+?[1-9]\d{6,14}$/'],
            'Called' => ['nullable', 'string', 'max:20', 'regex:/^\+?[1-9]\d{6,14}$/'],
            'CallSid' => ['nullable', 'string', 'regex:/^CA[a-fA-F0-9]{32}$/'],
            'RecordingSid' => ['nullable', 'string', 'regex:/^RE[a-fA-F0-9]{32}$/'],
            'RecordingStatus' => ['nullable', 'string', 'max:32'],
            'RecordingUrl' => ['nullable', 'string', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'CallSid.regex' => 'El CallSid recibido no tiene el formato esperado.',
            'RecordingSid.regex' => 'El RecordingSid recibido no tiene el formato esperado.',
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
        $value = trim((string) $this->validated('CallSid', ''));

        return $value === '' ? null : $value;
    }

    public function recordingStatus(): ?string
    {
        $value = trim((string) $this->validated('RecordingStatus', ''));

        return $value === '' ? null : $value;
    }

    public function recordingUrl(): ?string
    {
        $value = trim((string) $this->validated('RecordingUrl', ''));

        return $value === '' ? null : $value;
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
