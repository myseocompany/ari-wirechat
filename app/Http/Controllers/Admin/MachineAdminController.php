<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IssueMachineTokenRequest;
use App\Http\Requests\Admin\StoreMachineRequest;
use App\Http\Requests\Admin\UpdateMachineRequest;
use App\Models\Customer;
use App\Models\Machine;
use App\Models\MachineCustomerHistory;
use App\Models\MachineToken;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MachineAdminController extends Controller
{
    public function index(): View
    {
        $machines = Machine::query()
            ->with('currentCustomer:id,name')
            ->withCount([
                'tokens',
                'productionMinutes',
                'faultEvents',
            ])
            ->orderBy('serial')
            ->paginate(20);

        return view('admin.machines.index', [
            'machines' => $machines,
        ]);
    }

    public function create(): View
    {
        return view('admin.machines.create', [
            'machine' => new Machine,
            'customers' => $this->customerOptions(),
        ]);
    }

    public function store(StoreMachineRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $machine = Machine::query()->create([
            'serial' => $validated['serial'],
            'current_customer_id' => $validated['current_customer_id'] ?? null,
        ]);

        $this->syncCustomerHistory($machine, $machine->current_customer_id);

        return redirect()
            ->route('admin.machines.show', $machine)
            ->with('status', 'Máquina creada correctamente.');
    }

    public function show(Machine $machine): View
    {
        $machine->load('currentCustomer:id,name');

        $history = MachineCustomerHistory::query()
            ->with('customer:id,name')
            ->where('machine_id', $machine->id)
            ->orderByDesc('start_at')
            ->limit(20)
            ->get();

        $tokens = MachineToken::query()
            ->where('machine_id', $machine->id)
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        $reports = $machine->reports()
            ->latest('received_at')
            ->limit(20)
            ->get(['id', 'batch_id', 'reported_at', 'received_at', 'created_at']);

        $minutes = $machine->productionMinutes()
            ->latest('minute_at')
            ->limit(20)
            ->get(['id', 'minute_at', 'tacometer_total', 'units_in_minute', 'is_backfill', 'received_at']);

        $faults = $machine->faultEvents()
            ->latest('reported_at')
            ->limit(20)
            ->get(['id', 'fault_code', 'severity', 'reported_at', 'metadata']);

        return view('admin.machines.show', [
            'machine' => $machine,
            'history' => $history,
            'tokens' => $tokens,
            'reports' => $reports,
            'minutes' => $minutes,
            'faults' => $faults,
        ]);
    }

    public function edit(Machine $machine): View
    {
        return view('admin.machines.edit', [
            'machine' => $machine,
            'customers' => $this->customerOptions(),
        ]);
    }

    public function update(UpdateMachineRequest $request, Machine $machine): RedirectResponse
    {
        $validated = $request->validated();

        $machine->fill([
            'serial' => $validated['serial'],
            'current_customer_id' => $validated['current_customer_id'] ?? null,
        ]);
        $machine->save();

        $this->syncCustomerHistory($machine, $machine->current_customer_id);

        return redirect()
            ->route('admin.machines.show', $machine)
            ->with('status', 'Máquina actualizada correctamente.');
    }

    public function issueToken(IssueMachineTokenRequest $request, Machine $machine): RedirectResponse
    {
        $plainToken = Str::random(64);

        MachineToken::query()->create([
            'machine_id' => $machine->id,
            'token_hash' => hash('sha256', $plainToken),
            'revoked_at' => null,
            'last_used_at' => null,
        ]);

        return redirect()
            ->route('admin.machines.show', $machine)
            ->with('status', 'Token emitido correctamente.')
            ->with('issued_machine_token', $plainToken);
    }

    public function revokeToken(Machine $machine, MachineToken $token): RedirectResponse
    {
        if ((int) $token->machine_id !== (int) $machine->id) {
            abort(404);
        }

        if (! $token->revoked_at) {
            $token->forceFill([
                'revoked_at' => now(),
            ])->save();
        }

        return redirect()
            ->route('admin.machines.show', $machine)
            ->with('status', 'Token revocado correctamente.');
    }

    /**
     * @return array<int, string>
     */
    private function customerOptions(): array
    {
        return Customer::query()
            ->orderBy('name')
            ->limit(500)
            ->pluck('name', 'id')
            ->toArray();
    }

    private function syncCustomerHistory(Machine $machine, ?int $customerId): void
    {
        $now = Carbon::now();
        $openHistory = MachineCustomerHistory::query()
            ->where('machine_id', $machine->id)
            ->whereNull('end_at')
            ->latest('start_at')
            ->first();

        if (! $customerId) {
            if ($openHistory) {
                $openHistory->forceFill([
                    'end_at' => $now,
                ])->save();
            }

            return;
        }

        if ($openHistory && (int) $openHistory->customer_id === (int) $customerId) {
            return;
        }

        if ($openHistory) {
            $openHistory->forceFill([
                'end_at' => $now,
            ])->save();
        }

        MachineCustomerHistory::query()->create([
            'machine_id' => $machine->id,
            'customer_id' => $customerId,
            'start_at' => $now,
            'end_at' => null,
        ]);
    }
}
