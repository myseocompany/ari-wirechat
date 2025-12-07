<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerTagController extends Controller
{
    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'tags' => ['nullable', 'array'],
            'tags.*' => ['integer', 'exists:tags,id'],
        ]);

        $customer->tags()->sync($data['tags'] ?? []);

        if ($request->ajax()) {
            return response()->json(['message' => 'Etiquetas del cliente actualizadas.']);
        }

        return back()->with('status', 'Etiquetas del cliente actualizadas.');
    }
}
