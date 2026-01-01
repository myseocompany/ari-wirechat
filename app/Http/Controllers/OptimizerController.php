<?php

namespace App\Http\Controllers;

use App\Models\Action;
use App\Models\Customer;
use App\Models\CustomerFile;
use App\Models\CustomerHistory;
use App\Models\CustomerMeta;
use App\Models\CustomerMetaData;
use App\Models\CustomerSource;
use App\Models\CustomerStatus;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Namu\WireChat\Models\Message;
use Namu\WireChat\Models\Participant;

class OptimizerController extends Controller
{
    public function consolidateDuplicates(Request $request)
    {
        $query = trim((string) $request->input('query'));

        if ($query === '') {
            return back()->with('error', 'Debe ingresar un correo o un teléfono para buscar duplicados.');
        }

        // Normaliza si es teléfono (solo dígitos)
        $digits = preg_replace('/\D/', '', $query);

        $model = Customer::query()
            ->when($digits !== '' && strlen($digits) >= 5, function ($q) use ($digits) {
                // Búsqueda por teléfono: prefijo en múltiples columnas
                $q->where('phone', 'like', $digits.'%')
                    ->orWhere('phone2', 'like', $digits.'%')
                    ->orWhere('contact_phone2', 'like', $digits.'%')
                    ->orWhere('phone_wp', 'like', $digits.'%');
            }, function ($q) use ($query) {
                // Búsqueda por email (o texto)
                $q->where('email', 'like', "%{$query}%");
            })
            ->with([
                // para getModelText():
                'status', 'source', 'user', 'updated_user', 'product',
                // para la vista:
                'actions.type', 'actions.creator', 'files',
            ])
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        if ($model->isEmpty()) {
            return back()->with('error', 'No se encontraron duplicados.');
        }

        $statuses_options = CustomerStatus::all();
        $products = Product::all();
        $customers_source = CustomerSource::all();
        $user = User::where('status_id', 1)
            ->orderBy('name')
            ->get();

        // Metadatos asociados a estos customers (para mostrar y consolidar)
        $metaByCustomer = collect();
        $metaNames = collect();
        $customerIds = $model->pluck('id');
        if ($customerIds->isNotEmpty()) {
            $metaByCustomer = CustomerMeta::whereIn('customer_id', $customerIds)->get()->groupBy('customer_id');
            $metaIds = $metaByCustomer->flatten()->pluck('meta_data_id')->filter()->unique();
            if ($metaIds->isNotEmpty()) {
                $metaNames = CustomerMetaData::whereIn('id', $metaIds)->get()->keyBy('id');
            }
        }

        $controller = $this;

        return view('optimizer.show', compact('model', 'statuses_options', 'controller', 'products', 'customers_source', 'user', 'metaByCustomer', 'metaNames'));
    }

    public function mergeDuplicates(Request $request)
    {
        // 0) Validación básica
        $data = $request->validate([
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'customer_id_all' => ['required', 'array', 'min:1'],
            'customer_id_all.*' => ['integer', 'distinct', 'exists:customers,id'],

            // campos editables (pon aquí solo los que debes permitir)
            'status_id' => ['nullable', 'integer'],
            'name' => ['nullable', 'string', 'max:100'],
            'document' => ['nullable', 'string', 'max:100'],
            'position' => ['nullable', 'string', 'max:100'],
            'business' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:50'],
            'phone2' => ['nullable', 'string', 'max:50'],
            'contact_phone2' => ['nullable', 'string', 'max:50'],
            'phone_wp' => ['nullable', 'string', 'max:255'],
            'total_sold' => ['nullable', 'integer'],
            'email' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:200'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'department' => ['nullable', 'string', 'max:200'],
            'contact_name' => ['nullable', 'string', 'max:250'],
            'contact_email' => ['nullable', 'string', 'max:250'],
            'contact_position' => ['nullable', 'string', 'max:250'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'source_id' => ['nullable', 'integer'],
            'purchase_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'technical_visit' => ['nullable', 'string'],
            'gender' => ['nullable', 'string', 'max:2'],
            'scoring_interest' => ['nullable', 'integer'],
            'scoring_profile' => ['nullable', 'string', 'max:1'],
            'rd_public_url' => ['nullable', 'string', 'max:250'],
            'src' => ['nullable', 'string', 'max:100'],
            'cid' => ['nullable', 'string', 'max:100'],
            'vas' => ['nullable', 'integer'],
            'rd_source' => ['nullable', 'string', 'max:250'],
            'product_id' => ['nullable', 'integer'],
            'country2' => ['nullable', 'string', 'max:250'],
            'count_empanadas' => ['nullable', 'string', 'max:255'],

            // listas seleccionadas en la UI (opcionales)
            'action_all' => ['sometimes', 'array'],
            'action_all.*' => ['integer', 'exists:actions,id'],
            'file_all' => ['sometimes', 'array'],
            'file_all.*' => ['integer', 'exists:customer_files,id'],
        ]);

        $winnerId = (int) $data['customer_id'];
        $ids = array_map('intval', $data['customer_id_all']);

        if (! in_array($winnerId, $ids, true)) {
            return back()->with('error', 'El registro principal no está dentro del conjunto de duplicados.');
        }

        // Cambia aquí si creas un tipo específico “Fusión de duplicados”
        $MERGE_TYPE_ID = 16; // "in: Actualización de entrada"

        DB::transaction(function () use ($winnerId, $ids, $data, $MERGE_TYPE_ID) {
            // Carga y bloquea
            $customers = Customer::whereIn('id', $ids)->lockForUpdate()->get()->keyBy('id');

            /** @var Customer $winner */
            $winner = $customers[$winnerId];
            $before = $winner->getAttributes(); // snapshot para log

            // Actualiza campos permitidos con lo que venga del form
            $updatable = [
                'status_id', 'name', 'document', 'position', 'business',
                'phone', 'phone2', 'contact_phone2', 'phone_wp', 'total_sold',
                'email', 'address', 'city', 'country', 'department',
                'contact_name', 'contact_email', 'contact_position',
                'user_id', 'source_id', 'purchase_date', 'notes', 'technical_visit',
                'gender', 'scoring_interest', 'scoring_profile', 'rd_public_url',
                'src', 'cid', 'vas', 'rd_source', 'product_id', 'country2', 'count_empanadas',
            ];
            foreach ($updatable as $f) {
                if (array_key_exists($f, $data)) {
                    $winner->{$f} = $data[$f];
                }
            }

            // Si no mandaste phone_wp, derive “mejor” número (simple: el más largo)
            if (! array_key_exists('phone_wp', $data)) {
                $phones = [
                    preg_replace('/\D/', '', (string) $winner->phone),
                    preg_replace('/\D/', '', (string) $winner->phone2),
                    preg_replace('/\D/', '', (string) $winner->contact_phone2),
                ];
                $phones = array_values(array_filter($phones));
                if (! empty($phones)) {
                    usort($phones, fn ($a, $b) => strlen($b) <=> strlen($a));
                    $winner->phone_wp = $phones[0];
                }
            }

            // Campos que efectivamente cambiaron
            $dirtyFields = array_keys($winner->getDirty());
            $winner->save();

            // Mover relaciones
            $others = array_values(array_diff($ids, [$winnerId]));

            if (! empty($others)) {
                // Actions
                if (! empty($data['action_all'] ?? [])) {
                    Action::whereIn('id', $data['action_all'])->update(['customer_id' => $winnerId]);
                } else {
                    Action::whereIn('customer_id', $others)->update(['customer_id' => $winnerId]);
                }

                // Files
                if (! empty($data['file_all'] ?? [])) {
                    CustomerFile::whereIn('id', $data['file_all'])->update(['customer_id' => $winnerId]);
                } else {
                    CustomerFile::whereIn('customer_id', $others)->update(['customer_id' => $winnerId]);
                }

                // Customer metas (encuestas / metadata)
                CustomerMeta::whereIn('customer_id', $others)->update(['customer_id' => $winnerId]);

                // History
                CustomerHistory::whereIn('customer_id', $others)->update(['customer_id' => $winnerId]);

                $winnerMorphClass = $winner->getMorphClass();

                Message::withoutGlobalScopes()
                    ->withTrashed()
                    ->where('sendable_type', $winnerMorphClass)
                    ->whereIn('sendable_id', $others)
                    ->update(['sendable_id' => $winnerId]);

                $winnerConversationIds = Participant::withoutGlobalScopes()
                    ->where('participantable_type', $winnerMorphClass)
                    ->where('participantable_id', $winnerId)
                    ->pluck('conversation_id');

                if ($winnerConversationIds->isNotEmpty()) {
                    $duplicateParticipantIds = Participant::withoutGlobalScopes()
                        ->where('participantable_type', $winnerMorphClass)
                        ->whereIn('participantable_id', $others)
                        ->whereIn('conversation_id', $winnerConversationIds)
                        ->pluck('id');

                    if ($duplicateParticipantIds->isNotEmpty()) {
                        Participant::withoutGlobalScopes()
                            ->whereIn('id', $duplicateParticipantIds)
                            ->delete();
                    }
                }

                Participant::withoutGlobalScopes()
                    ->where('participantable_type', $winnerMorphClass)
                    ->whereIn('participantable_id', $others)
                    ->update(['participantable_id' => $winnerId]);

                // Borrar duplicados
                Customer::whereIn('id', $others)->delete();
            }

            // ===== Crear la acción de auditoría de fusión =====
            $actorId = Auth::id();
            $deletedList = collect($others)->map(function ($id) use ($customers) {
                $n = optional($customers->get($id))->name;

                return "#{$id}".($n ? " ({$n})" : '');
            })->implode(', ');

            $changed = empty($dirtyFields) ? '—' : implode(', ', $dirtyFields);

            $note = "🔗 Fusión de duplicados\n".
                    "Principal: #{$winnerId} (".($winner->name ?? '').")\n".
                    'Eliminados: '.($deletedList ?: '—')."\n".
                    "Campos actualizados: {$changed}";

            // Si tu modelo Action no tiene $fillable, usa DB::table()->insert(...)
            Action::create([
                'note' => $note,
                'customer_id' => $winnerId,
                'customer_owner_id' => $before['user_id'] ?? null,
                'customer_createad_at' => $before['created_at'] ?? null,
                'customer_updated_at' => now(),
                'type_id' => $MERGE_TYPE_ID,
                'creator_user_id' => $actorId,
                // opcional: 'owner_user_id' => $winner->user_id,
                // opcional: 'url' => request()->fullUrl(),
            ]);
        });

        return redirect()
            ->route('optimizer.consolidate', ['query' => $request->input('query', $request->input('email', ''))])
            ->with('success', 'Consolidación realizada y registrada.');
    }

    // Devuelve una etiqueta corta para los campos relacionales
    public function getModelText(string $key, $model): string
    {
        // Usa optional() para evitar errores cuando no hay relación cargada
        switch ($key) {
            case 'status_id':
                return optional($model->status)->name ? optional($model->status)->name.' - ' : '';
            case 'source_id':
                return optional($model->source)->name ? optional($model->source)->name.' - ' : '';
            case 'user_id':
                return optional($model->user)->name ? optional($model->user)->name.' - ' : '';
            case 'updated_user_id': // si tienes esta relación en Customer
                return optional($model->updated_user)->name ? optional($model->updated_user)->name.' - ' : '';
            case 'product_id':
                return optional($model->product)->name ? optional($model->product)->name.' - ' : '';
            default:
                return '';
        }
    }
}
