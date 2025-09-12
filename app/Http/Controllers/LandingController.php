<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LandingController extends Controller
{
    public function checkinForm(Request $request)
    {
        return view('madrid.checkin'); // Primera pantalla: solo teléfono
    }

    public function checkinSubmit(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        $customer = DB::table('customers')
            ->where('phone', $request->phone)
            ->first();

        if (!$customer) {
            return back()->withErrors(['phone' => 'No se encontró un cliente con ese teléfono.']);
        }

        $nombre = $customer->name ?? 'Cliente';

        // Traer la cita actual type_id=101
        $cita = DB::table('actions')
            ->where('customer_id', $customer->id)
            ->where('type_id', 101)
            ->orderBy('due_date')
            ->first();

        // Resumen de slots
        $dates = [
            Carbon::create(2025, 9, 16),
            Carbon::create(2025, 9, 17)
        ];

        $acciones = DB::table('actions')
            ->join('customers', 'actions.customer_id', '=', 'customers.id')
            ->where('actions.type_id', 101)
            ->whereBetween('actions.due_date', [$dates[0], $dates[1]->endOfDay()])
            ->select(
                'customers.id as customer_id',
                DB::raw("DATE_FORMAT(actions.due_date, '%Y-%m-%d %H:00:00') as slot")
            )
            ->distinct()
            ->get();

        $resumen = [];
        foreach ($dates as $fecha) {
            $f = $fecha->format('Y-m-d');
            for ($h = 9; $h <= 17; $h++) {
                $slot = Carbon::parse("$f $h:00:00")->format('Y-m-d H:00:00');
                $key = "$f-$h";
                $resumen[$key] = $acciones->where('slot', $slot)->pluck('customer_id')->unique()->count();
            }
        }

        return view('madrid.checkin', compact('customer','nombre','cita','resumen'));
    }

public function rebook(Request $request)
{
    $request->validate([
        'phone' => 'required|string',
        'date'  => 'required|date|in:2025-09-16,2025-09-17',
        'hour'  => 'required|integer|min:9|max:17',
    ]);

    $customer = DB::table('customers')
        ->where('phone', $request->phone)
        ->first();

    if (!$customer) {
        return response()->json([
            'error' => 'No se encontró cliente con ese teléfono.'
        ], 404);
    }

    // Obtener acción actual type_id=101
    $currentAction = DB::table('actions')
        ->where('customer_id', $customer->id)
        ->where('type_id', 101)
        ->first();

    if (!$currentAction) {
        return response()->json([
            'error' => 'No se encontró cita existente para este cliente.'
        ], 404);
    }

    $oldDate = $currentAction->due_date;
    $newDate = Carbon::parse($request->date . ' ' . $request->hour . ':00:00');

    // Actualizar la acción existente
    DB::table('actions')
        ->where('id', $currentAction->id)
        ->update([
            'due_date' => $newDate,
            'updated_at' => now(),
        ]);

    // Crear nueva acción tipo 16 que registre el cambio
    DB::table('actions')->insert([
        'customer_id' => $customer->id,
        'type_id' => 16, // tipo "reagendamiento"
        'note' => "El cliente reagendó cita. Antes estaba en " . $oldDate . " y quedó en " . $newDate,
        'creator_user_id' => null,
        'owner_user_id' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return response()->json([
        'success' => "Cita reagendada correctamente: de $oldDate a $newDate."
    ]);
}


public function cancel(Request $request)
{
    $request->validate(['phone' => 'required|string']);

    $customer = DB::table('customers')->where('phone', $request->phone)->first();
    if(!$customer){
        return response()->json(['error'=>'No se encontró el cliente'],404);
    }

    DB::table('actions')
      ->where('customer_id', $customer->id)
      ->where('type_id', 101)
      ->delete();

    return response()->json(['success'=>'Cita cancelada correctamente']);
}

}
