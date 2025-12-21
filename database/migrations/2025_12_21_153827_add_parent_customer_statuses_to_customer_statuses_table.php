<?php

use App\Models\CustomerStatus;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $parentDefinitions = [
            'Por contactar' => [
                'color' => '#3B6CFF',
                'weight' => 1,
            ],
            'Oportunidades' => [
                'color' => '#6A8BFF',
                'weight' => 2,
            ],
            'Ventas' => [
                'color' => '#2FC677',
                'weight' => 3,
            ],
            'Perdidas' => [
                'color' => '#9BA9BC',
                'weight' => 4,
            ],
        ];

        $parentIds = [];
        $nextId = (int) CustomerStatus::query()->max('id') + 1;

        foreach ($parentDefinitions as $name => $definition) {
            $status = CustomerStatus::query()->where('name', $name)->first() ?? new CustomerStatus;
            if (! $status->exists) {
                $status->id = $nextId;
                $nextId++;
            }
            $status->name = $name;
            $status->color = $definition['color'];
            $status->weight = $definition['weight'];
            $status->status_id = $status->status_id ?? 1;
            $status->stage_id = $status->stage_id ?? 1;
            $status->parent_id = null;
            $status->save();

            $parentIds[$name] = $status->id;
        }

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
                CustomerStatus::query()
                    ->where('name', $childName)
                    ->update(['parent_id' => $parentId]);
            }
        }
    }

    public function down(): void
    {
        $parentNames = ['Por contactar', 'Oportunidades', 'Ventas', 'Perdidas'];
        $parentIds = CustomerStatus::query()
            ->whereIn('name', $parentNames)
            ->pluck('id');

        CustomerStatus::query()
            ->whereIn('parent_id', $parentIds)
            ->update(['parent_id' => null]);

        CustomerStatus::query()
            ->whereIn('name', $parentNames)
            ->delete();
    }
};
