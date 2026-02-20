<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMessageSourceRequest;
use App\Http\Requests\UpdateMessageSourceRequest;
use App\Models\MessageSource;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;

class MessageSourceController extends Controller
{
    public function index(): View
    {
        $messageSources = MessageSource::query()
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->paginate(20);

        return view('message_sources.index', [
            'messageSources' => $messageSources,
            'hasPhoneNumberColumn' => $this->hasPhoneNumberColumn(),
        ]);
    }

    public function create(): View
    {
        return view('message_sources.create', [
            'messageSource' => new MessageSource,
            'hasPhoneNumberColumn' => $this->hasPhoneNumberColumn(),
        ]);
    }

    public function store(StoreMessageSourceRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $isDefault = (bool) Arr::get($validated, 'is_default', false);

        $this->clearCurrentDefaultIfNeeded($isDefault);

        MessageSource::query()->create(
            $this->buildPayload($validated, null)
        );

        return redirect()
            ->route('message-sources.index')
            ->with('status', 'Línea de WhatsApp creada correctamente.');
    }

    public function edit(MessageSource $messageSource): View
    {
        return view('message_sources.edit', [
            'messageSource' => $messageSource,
            'hasPhoneNumberColumn' => $this->hasPhoneNumberColumn(),
        ]);
    }

    public function update(UpdateMessageSourceRequest $request, MessageSource $messageSource): RedirectResponse
    {
        $validated = $request->validated();
        $isDefault = (bool) Arr::get($validated, 'is_default', false);

        $this->clearCurrentDefaultIfNeeded($isDefault, $messageSource->id);

        $messageSource->update(
            $this->buildPayload($validated, $messageSource)
        );

        return redirect()
            ->route('message-sources.index')
            ->with('status', 'Línea de WhatsApp actualizada correctamente.');
    }

    public function destroy(MessageSource $messageSource): RedirectResponse
    {
        $messageSource->delete();

        return redirect()
            ->route('message-sources.index')
            ->with('status', 'Línea de WhatsApp eliminada.');
    }

    private function buildPayload(array $validated, ?MessageSource $messageSource): array
    {
        $payload = [
            'type' => $validated['type'],
            'APIKEY' => $validated['APIKEY'],
            'is_default' => (bool) Arr::get($validated, 'is_default', false),
        ];

        if ($this->hasPhoneNumberColumn()) {
            $payload['phone_number'] = Arr::get($validated, 'phone_number');
        }

        $settings = $messageSource && is_array($messageSource->settings)
            ? $messageSource->settings
            : [];

        $settings['active'] = Arr::has($validated, 'active')
            ? (bool) Arr::get($validated, 'active')
            : (bool) ($settings['active'] ?? true);

        $sourceId = Arr::get($validated, 'source_id');
        if ($sourceId !== null && $sourceId !== '') {
            $settings['source_id'] = (int) $sourceId;
        } else {
            unset($settings['source_id']);
        }

        $webhookUrl = trim((string) Arr::get($validated, 'webhook_url', ''));
        if ($webhookUrl !== '') {
            $settings['webhook_url'] = $webhookUrl;
        } else {
            unset($settings['webhook_url']);
        }

        $payload['settings'] = $settings;

        return $payload;
    }

    private function clearCurrentDefaultIfNeeded(bool $isDefault, ?int $excludedId = null): void
    {
        if (! $isDefault) {
            return;
        }

        $query = MessageSource::query()->where('is_default', true);

        if ($excludedId !== null) {
            $query->whereKeyNot($excludedId);
        }

        $query->update(['is_default' => false]);
    }

    private function hasPhoneNumberColumn(): bool
    {
        return Schema::hasColumn('message_sources', 'phone_number');
    }
}
