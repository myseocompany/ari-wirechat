<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $parentIds = DB::table('customer_statuses')
            ->whereIn('name', ['Por contactar', 'Oportunidades', 'Ventas', 'Perdidas'])
            ->pluck('id', 'name');

        $childrenByParent = [
            'Por contactar' => [
                'Nuevo',
                'Int 1',
                'Int 2',
                'Int 3',
                'Recontacto',
                'Reingreso',
            ],
            'Oportunidades' => [
                'Interesado',
                'Calificado',
                'Demo',
                'Negociación',
                'Gestión Compra',
                'Agendó cita',
            ],
            'Ventas' => [
                'Ganado Maquinas',
                'Ganado otros',
            ],
            'Perdidas' => [
                'Perdido',
                'Mal calificado',
                'Pidió la baja',
                'Repetido',
                'No contesta',
                'Buscando',
            ],
        ];

        foreach ($childrenByParent as $parentName => $children) {
            $parentId = $parentIds[$parentName] ?? null;
            if (! $parentId) {
                continue;
            }

            foreach ($children as $childName) {
                if ($childName === 'Agendó cita') {
                    DB::statement("UPDATE customer_statuses SET parent_id = {$parentId} WHERE TRIM(name) = _utf8mb4'Agendó cita'");
                    continue;
                }

                DB::table('customer_statuses')
                    ->where(DB::raw('TRIM(name)'), '=', $childName)
                    ->update(['parent_id' => $parentId]);
            }
        }
    }

    public function down(): void
    {
        $parentIds = DB::table('customer_statuses')
            ->whereIn('name', ['Por contactar', 'Oportunidades', 'Ventas', 'Perdidas'])
            ->pluck('id');

        DB::table('customer_statuses')
            ->whereIn('parent_id', $parentIds)
            ->update(['parent_id' => null]);
    }
};
