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
    public function index(Request $request) {
        $request->merge([
            'from_date' => $request->query('from_date'),
            'to_date'   => $request->query('to_date'),
        ]);
        $pageSize = (int) $request->query('per_page', 50);
        $result = $this->svc->filterCustomers($request, [], null, false, $pageSize);

        return response()->json([
            'data' => $result->items(),
            'meta' => [
                'current_page' => $result->currentPage(),
                'per_page'     => $result->perPage(),
                'total'        => $result->total(),
                'benchmark_ms' => $result->benchmark_ms ?? null,
            ],
        ]);
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
