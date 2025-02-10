<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;


class RDTestController extends Controller
{

    public function test(){

        
        
        return view('rdtest');
     
    }
    public function handleRequest(Request $request)
{
    // Decodificar el JSON del formulario
    $data = json_decode($request->json_data, true);

    if (!$data || !isset($data['leads'])) {
        return back()->with('error', 'Formato de JSON inválido');
    }

    // Validar los datos decodificados
    $validated = validator($data, [
        'leads' => 'required|array',
        'leads.*.id' => 'required|string',
        'leads.*.email' => 'required|email',
        'leads.*.name' => 'required|string',
        'leads.*.company' => 'nullable|string',
        'leads.*.job_title' => 'nullable|string',
        'leads.*.public_url' => 'nullable|url',
        'leads.*.created_at' => 'required|date',
        'leads.*.opportunity' => 'required|string',
        'leads.*.custom_fields' => 'nullable|array',
    ])->validate();

    // Guardar en logs para depuración
    Log::info('Datos recibidos:', $validated);

    return response()->json([
        'message' => 'Datos recibidos correctamente',
        'data' => $validated
    ], 200);
}

}
