<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssociateRetellInboxCustomerRequest extends FormRequest
{
    protected $errorBag = 'retellAssociation';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'call_id' => ['required', 'string', 'max:191', 'exists:retell_inbox,call_id'],
            'customer_id' => ['required', 'integer', 'min:1', 'exists:customers,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'call_id.exists' => 'La llamada de Retell no existe en inbox.',
            'customer_id.exists' => 'El cliente indicado no existe.',
        ];
    }
}
