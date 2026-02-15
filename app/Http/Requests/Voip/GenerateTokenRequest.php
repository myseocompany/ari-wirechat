<?php

namespace App\Http\Requests\Voip;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class GenerateTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }

    public function resolvedIdentity(): string
    {
        $user = $this->user();
        $authId = (string) ($user?->getAuthIdentifier() ?? 'guest');
        $rawName = trim((string) ($user?->name ?? 'user'));

        $normalizedName = Str::slug(Str::ascii(Str::lower($rawName)));
        $normalizedName = trim($normalizedName, '-');
        $normalizedName = $normalizedName === '' ? 'user' : $normalizedName;
        $identity = $normalizedName.'-'.$authId;

        return Str::limit($identity, 80, '');
    }
}
