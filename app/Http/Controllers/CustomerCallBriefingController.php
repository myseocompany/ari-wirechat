<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerCallBriefingRequest;
use App\Jobs\GenerateCustomerCallBriefing;
use App\Models\Customer;
use App\Models\CustomerCallBriefing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;

class CustomerCallBriefingController extends Controller
{
    public function store(StoreCustomerCallBriefingRequest $request, Customer $customer): JsonResponse|RedirectResponse
    {
        abort_unless(app_feature_enabled('customer_call_briefing_enabled', false), 404);

        $customerKey = "customer-call-briefing:customer:{$customer->id}";
        $userKey = "customer-call-briefing:user:{$request->user()->id}";
        if (RateLimiter::tooManyAttempts($customerKey, 1) || RateLimiter::tooManyAttempts($userKey, 30)) {
            return $this->response($request, 'Espera un momento antes de volver a preparar esta llamada.', 429, 'statustwo');
        }

        RateLimiter::hit($customerKey, 60);
        RateLimiter::hit($userKey, 3600);

        $shouldDispatch = DB::transaction(function () use ($customer, $request): bool {
            CustomerCallBriefing::query()->insertOrIgnore([
                'customer_id' => $customer->id,
                'status' => 'failed',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $briefing = CustomerCallBriefing::query()->lockForUpdate()->findOrFail($customer->id);
            if ($briefing->status === 'pending' && $briefing->updated_at?->greaterThan(now()->subMinutes(3))) {
                return false;
            }

            $briefing->fill([
                'status' => 'pending',
                'requested_by_user_id' => $request->user()->id,
                'requested_at' => now(),
                'error_code' => null,
                'error_message' => null,
            ])->save();

            return true;
        });

        if ($shouldDispatch) {
            GenerateCustomerCallBriefing::dispatch($customer->id)->afterCommit();
        }

        return $this->response(
            $request,
            $shouldDispatch ? 'Estamos preparando la llamada.' : 'Ya hay una preparación en curso para este cliente.',
            202,
        );
    }

    private function response(StoreCustomerCallBriefingRequest $request, string $message, int $status, string $flashKey = 'status'): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], $status);
        }

        return back()->with($flashKey, $message);
    }
}
