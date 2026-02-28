<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesAudienceSegment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreAudienceSegmentRequest extends FormRequest
{
    use ValidatesAudienceSegment;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:200'],
            'segment_json' => ['required', 'string', 'json', 'max:100000'],
            'max_recipients' => ['nullable', 'integer', 'min:1', 'max:50000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la audiencia es obligatorio.',
            'segment_json.required' => 'Debes enviar la definición del segmento.',
            'segment_json.json' => 'La definición del segmento debe estar en formato JSON válido.',
            'max_recipients.integer' => 'El límite debe ser numérico.',
            'max_recipients.min' => 'El límite mínimo es 1.',
            'max_recipients.max' => 'El límite máximo permitido es 50.000.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $this->validateAudienceSegment($validator, true);
        });
    }
}
