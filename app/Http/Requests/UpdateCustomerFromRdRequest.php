<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerFromRdRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'phone' => ['required_with:tag_id', 'string', 'max:50'],
            'tag_id' => ['required_with:phone', 'integer', 'exists:tags,id'],
            'leads' => ['required_without_all:phone,tag_id', 'array'],
            'leads.0' => ['required_without_all:phone,tag_id', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required_with' => 'El teléfono es obligatorio cuando envías tag_id.',
            'phone.string' => 'El teléfono debe ser texto.',
            'phone.max' => 'El teléfono no puede tener más de 50 caracteres.',
            'tag_id.required_with' => 'La etiqueta es obligatoria cuando envías phone.',
            'tag_id.integer' => 'La etiqueta debe ser un número.',
            'tag_id.exists' => 'La etiqueta indicada no existe.',
            'leads.required_without_all' => 'Debes enviar leads o phone y tag_id.',
            'leads.array' => 'El campo leads debe ser un arreglo.',
            'leads.0.required_without_all' => 'Debes enviar al menos un lead o phone y tag_id.',
            'leads.0.array' => 'El lead debe ser un arreglo.',
        ];
    }
}
