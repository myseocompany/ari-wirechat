<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreMachineRequest extends FormRequest
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
            'serial' => ['required', 'string', 'max:80', 'unique:machines,serial'],
            'current_customer_id' => ['nullable', 'integer', 'exists:customers,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'serial.required' => 'El serial es obligatorio.',
            'serial.unique' => 'Ya existe una máquina con ese serial.',
            'current_customer_id.exists' => 'El customer seleccionado no existe.',
        ];
    }
}
