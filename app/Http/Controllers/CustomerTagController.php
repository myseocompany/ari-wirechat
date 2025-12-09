<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Tag;
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

    public function addMql(Request $request, Customer $customer)
    {
        $tag = Tag::where('name', 'MQL')->first() ?? Tag::find(1);

        if (! $tag) {
            return back()->with('statustwo', 'No se encontró la etiqueta MQL.');
        }

        $customer->tags()->syncWithoutDetaching([$tag->id]);

        if ($request->ajax()) {
            return response()->json(['message' => 'Cliente agregado como MQL.']);
        }

        $redirect = $request->input('redirect_to');
        if ($redirect) {
            return redirect($redirect)->with('status', 'Cliente agregado como MQL.');
        }

        return back()->with('status', 'Cliente agregado como MQL.');
    }
}
