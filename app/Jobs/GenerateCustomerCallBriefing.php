<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\CustomerCallBriefing;
use App\Services\CustomerCallBriefingContextBuilder;
use App\Services\CustomerCallBriefingGenerator;
use App\Services\CustomerCallBriefingPromptLoader;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Throwable;

class GenerateCustomerCallBriefing implements ShouldQueue
{
    use Queueable;

    public int $timeout = 90;

    public int $tries = 2;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $customerId) {}

    public function middleware(): array
    {
        return [(new WithoutOverlapping("customer-call-briefing:{$this->customerId}"))->expireAfter(180)->dontRelease()];
    }

    public function backoff(): array
    {
        return [15];
    }

    /**
     * Execute the job.
     */
    public function handle(
        CustomerCallBriefingContextBuilder $contextBuilder,
        CustomerCallBriefingPromptLoader $promptLoader,
        CustomerCallBriefingGenerator $generator,
    ): void {
        $customer = Customer::find($this->customerId);
        $briefing = CustomerCallBriefing::find($this->customerId);
        if (! $customer || ! $briefing || $briefing->status !== 'pending') {
            return;
        }

        $prompt = $promptLoader->load();
        $context = $contextBuilder->build($customer, $prompt['version'], $prompt['hash']);

        if (! $context['sufficient']) {
            $briefing->fill([
                'status' => 'insufficient_context',
                'source_hash' => $context['source_hash'],
                'prompt_version' => $prompt['version'],
                'prompt_hash' => $prompt['hash'],
                'generated_at' => now(),
                'model' => null,
                'error_code' => null,
                'error_message' => null,
            ])->save();

            return;
        }

        $result = $generator->generate($prompt, $context['context']);
        $briefing->fill([
            'status' => 'ready',
            'summary' => $result['summary'],
            'known_facts' => $result['known_facts'],
            'conflicting_facts' => $result['conflicting_facts'],
            'avoid_asking' => $result['avoid_asking'],
            'suggested_opening' => $result['suggested_opening'],
            'next_question' => $result['next_question'],
            'source_hash' => $context['source_hash'],
            'prompt_version' => $prompt['version'],
            'prompt_hash' => $prompt['hash'],
            'generated_at' => now(),
            'model' => $result['model'],
            'error_code' => null,
            'error_message' => null,
        ])->save();
    }

    public function failed(Throwable $exception): void
    {
        $this->markFailed($exception->getMessage());
    }

    private function markFailed(string $error): void
    {
        CustomerCallBriefing::query()->where('customer_id', $this->customerId)->update([
            'status' => 'failed',
            'error_code' => $this->errorCode($error),
            'error_message' => 'No pudimos preparar la llamada. Intenta nuevamente.',
            'updated_at' => now(),
        ]);
    }

    private function errorCode(string $error): string
    {
        return preg_match('/^[a-z0-9_]+$/', $error) ? $error : 'generation_failed';
    }
}
