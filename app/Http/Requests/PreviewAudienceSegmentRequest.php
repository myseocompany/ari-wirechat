<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesAudienceSegment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class PreviewAudienceSegmentRequest extends FormRequest
{
    use ValidatesAudienceSegment;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'segment_json' => ['required', 'string', 'json', 'max:100000'],
        ];
    }

    public function messages(): array
    {
        return [
            'segment_json.required' => 'Debes enviar la definición del segmento.',
            'segment_json.json' => 'La definición del segmento debe estar en formato JSON válido.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $this->validateAudienceSegment($validator, true);
        });
    }
}
