<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Tag;
use App\Services\MetaConversionsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CustomerTagController extends Controller
{
    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'tags' => ['nullable', 'array'],
            'tags.*' => ['integer', 'exists:tags,id'],
        ]);

        $previousTagIds = $customer->tags()->pluck('tags.id')->all();
        $customer->tags()->sync($data['tags'] ?? []);
        $customer->load('tags');
        $currentTagIds = $customer->tags->pluck('id')->all();
        $addedTagIds = array_diff($currentTagIds, $previousTagIds);

        if (!empty($addedTagIds)) {
            $this->dispatchMetaEventsForTags($customer, $addedTagIds);
        }

        if ($request->ajax()) {
            return response()->json(['message' => 'Etiquetas del cliente actualizadas.']);
        }

        return back()->with('status', 'Etiquetas del cliente actualizadas.');
    }

    public function addMql(Request $request, Customer $customer)
    {
        $tag = Tag::where('name', 'MQL')->first() ?? Tag::find(1);

        if (! $tag) {
            return back()->with('statustwo', 'No se encontró la etiqueta MQL.');
        }

        $customer->tags()->syncWithoutDetaching([$tag->id]);
        $this->dispatchMetaEventsForTags($customer, [$tag->id]);

        if ($request->ajax()) {
            return response()->json(['message' => 'Cliente agregado como MQL.']);
        }

        $redirect = $request->input('redirect_to');
        if ($redirect) {
            return redirect($redirect)->with('status', 'Cliente agregado como MQL.');
        }

        return back()->with('status', 'Cliente agregado como MQL.');
    }

    private function dispatchMetaEventsForTags(Customer $customer, array $tagIds): void
    {
        $tags = Tag::whereIn('id', $tagIds)->get();
        if ($tags->isEmpty()) {
            return;
        }

        $service = app(MetaConversionsService::class);
        if (! $service->isEnabled()) {
            Log::warning('MetaConversionsService disabled, skipping tag event dispatch', [
                'customer_id' => $customer->id,
                'tags' => $tags->pluck('slug'),
            ]);
            return;
        }

        foreach ($tags as $tag) {
            $eventName = $this->mapTagToEvent($tag);
            if (! $eventName) {
                continue;
            }

            try {
                $service->sendLeadEvent(
                    $customer,
                    $eventName,
                    now()->timestamp,
                    [
                        'lead_event_source' => 'ARI CRM',
                        'campaign_name' => $customer->campaign_name,
                    ]
                );
            } catch (\Throwable $e) {
                Log::error('Failed to send Meta event for tag', [
                    'customer_id' => $customer->id,
                    'tag' => $tag->slug ?? $tag->name,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function mapTagToEvent(Tag $tag): ?string
    {
        $slug = strtolower($tag->slug ?? $tag->name ?? '');
        return match ($slug) {
            'mql' => 'marketing_qualified_lead',
            'sql' => 'sales_opportunity',
            'converted' => 'converted',
            default => null,
        };
    }
}
