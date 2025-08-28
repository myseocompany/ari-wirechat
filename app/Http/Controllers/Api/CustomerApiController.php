<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\CustomerService;
use App\Models\Customer;
use App\Models\CustomerAction; // asume modelo
use Illuminate\Validation\Rule;

class CustomerApiController extends Controller
{
    public function __construct(private CustomerService $svc) {}

    // GET /api/customers
    public function index(Request $request)
{
    $query = Customer::query();

    // filtros normales (status_id, fechas, etc.)
    if ($request->has('status_id')) {
        $query->where('status_id', $request->status_id);
    }

    if ($request->has('from_date') && $request->has('to_date')) {
        $query->whereBetween('created_at', [$request->from_date, $request->to_date]);
    }

    // 🔎 FILTRO por acciones
    if ($request->has('action_note') || $request->has('action_from') || $request->has('action_to')) {
        $query->whereHas('actions', function ($q) use ($request) {
            if ($request->filled('action_note')) {
                $q->where('note', 'like', '%' . $request->action_note . '%');
            }
            if ($request->filled('action_from') && $request->filled('action_to')) {
                $q->whereBetween('created_at', [$request->action_from, $request->action_to]);
            }
        });
    }

    $customers = $query->paginate($request->get('per_page', 50));

    return response()->json($customers);
}


    // GET /api/customers/{id}
    public function show($id) {
        $c = Customer::with(['status','user','actions'])->findOrFail($id);
        return response()->json(['data' => $c]);
    }

    // PATCH /api/customers/{id}/status {status_id}
    public function updateStatus(Request $request, $id) {
        $this->authorize('update', Customer::class);
        $validated = $request->validate([
            'status_id' => ['required','integer','exists:customer_statuses,id'],
        ]);
        $c = Customer::findOrFail($id);
        $c->status_id = $validated['status_id'];
        $c->save();

        return response()->json(['ok' => true, 'data' => $c]);
    }

    // POST /api/customers/{id}/actions {type, due_at, note, assignee_id?}
    public function addAction(Request $request, $id) {
        $this->authorize('update', Customer::class);
        $validated = $request->validate([
            'type'        => ['required', Rule::in(['call','demo','quote','followup','meeting','whatsapp'])],
            'note'        => ['nullable','string','max:2000'],
            'due_at'      => ['required','date'],           // ISO 8601 desde n8n
            'assignee_id' => ['nullable','integer','exists:users,id'],
        ]);

        $c = Customer::findOrFail($id);
        $a = CustomerAction::create([
            'customer_id' => $c->id,
            'type'        => $validated['type'],
            'note'        => $validated['note'] ?? null,
            'due_at'      => $validated['due_at'],
            'assignee_id' => $validated['assignee_id'] ?? auth()->id(),
            'status'      => 'pending',
        ]);
        return response()->json(['ok'=>true,'data'=>$a], 201);
    }

    // POST /api/customers/bulk/status {ids:[], status_id}
    public function bulkUpdateStatus(Request $request) {
        $this->authorize('update', Customer::class);
        $v = $request->validate([
            'ids'       => ['required','array','min:1'],
            'ids.*'     => ['integer','exists:customers,id'],
            'status_id' => ['required','integer','exists:customer_statuses,id'],
        ]);
        Customer::whereIn('id',$v['ids'])->update(['status_id'=>$v['status_id']]);
        return response()->json(['ok'=>true,'updated'=>count($v['ids'])]);
    }

    // POST /api/customers/bulk/actions {ids:[], action:{...}}
    public function bulkAddAction(Request $request) {
        $this->authorize('update', Customer::class);
        $v = $request->validate([
            'ids'           => ['required','array','min:1'],
            'ids.*'         => ['integer','exists:customers,id'],
            'action.type'   => ['required', Rule::in(['call','demo','quote','followup','meeting','whatsapp'])],
            'action.note'   => ['nullable','string','max:2000'],
            'action.due_at' => ['required','date'],
            'action.assignee_id' => ['nullable','integer','exists:users,id'],
        ]);
        $payload = [];
        foreach ($v['ids'] as $cid) {
            $payload[] = [
                'customer_id' => $cid,
                'type'        => $v['action']['type'],
                'note'        => $v['action']['note'] ?? null,
                'due_at'      => $v['action']['due_at'],
                'assignee_id' => $v['action']['assignee_id'] ?? auth()->id(),
                'status'      => 'pending',
                'created_at'  => now(), 'updated_at'=>now(),
            ];
        }
        \DB::table('customer_actions')->insert($payload);
        return response()->json(['ok'=>true,'created'=>count($payload)]);
    }
}
