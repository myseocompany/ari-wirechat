<?php

namespace App\Http\Requests\Admin;

use App\Models\Machine;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMachineRequest extends FormRequest
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
        /** @var Machine $machine */
        $machine = $this->route('machine');

        return [
            'serial' => ['required', 'string', 'max:80', 'unique:machines,serial,'.$machine->id],
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
