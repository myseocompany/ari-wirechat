<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\CampaignMessage;
use App\Models\CustomerStatus;
use App\Models\Tag;
use App\Models\User;
use App\Models\WhatsAppAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class WhatsAppBroadcastController extends Controller
{
    public function create()
    {
        $accounts = WhatsAppAccount::with(['templates' => function ($query) {
            $query->orderBy('name');
        }])->orderByDesc('is_default')->orderBy('name')->get();

        $tags = Tag::orderBy('name')->get(['id', 'name']);
        $statuses = CustomerStatus::orderBy('name')->get(['id', 'name']);
        $users = User::orderBy('name')->get(['id', 'name']);

        $templateCatalog = $accounts->mapWithKeys(function ($account) {
            return [
                $account->id => $account->templates->map(function ($template) {
                    return [
                        'id' => $template->id,
                        'name' => $template->name,
                        'language' => $template->language,
                        'category' => $template->category,
                        'status' => $template->status,
                    ];
                })->values(),
            ];
        });

        return view('whatsapp_broadcasts.create', [
            'accounts' => $accounts,
            'tags' => $tags,
            'statuses' => $statuses,
            'users' => $users,
            'templateCatalog' => $templateCatalog->toArray(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'description' => ['nullable', 'string', 'max:500'],
            'account_id' => ['required', 'exists:whatsapp_accounts,id'],
            'template_id' => ['nullable', 'exists:whatsapp_templates,id'],
            'template_name' => ['required_without:template_id', 'string', 'max:190'],
            'template_language' => ['required', 'string', 'max:10'],
            'header_type' => ['required', Rule::in(['none', 'image', 'video', 'document'])],
            'header_media_url' => ['nullable', 'url', 'required_unless:header_type,none'],
            'body_parameters' => ['nullable', 'array'],
            'body_parameters.*.source' => ['nullable', 'string', 'max:100'],
            'body_parameters.*.fallback' => ['nullable', 'string', 'max:190'],
            'filters.search' => ['nullable', 'string', 'max:190'],
            'filters.tag_ids' => ['nullable', 'array'],
            'filters.tag_ids.*' => ['integer', 'exists:tags,id'],
            'filters.status_ids' => ['nullable', 'array'],
            'filters.status_ids.*' => ['integer', 'exists:customer_statuses,id'],
            'filters.owner_ids' => ['nullable', 'array'],
            'filters.owner_ids.*' => ['integer', 'exists:users,id'],
            'filters.has_phone' => ['nullable', 'boolean'],
            'filters.created_from' => ['nullable', 'date'],
            'filters.created_to' => ['nullable', 'date'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'wait_seconds' => ['required', 'integer', 'min:0', 'max:3600'],
            'action_note' => ['required', 'string', 'max:255'],
        ]);

        $filters = $validated['filters'] ?? [];
        $filters['tag_ids'] = array_values(array_filter($filters['tag_ids'] ?? []));
        $filters['status_ids'] = array_values(array_filter($filters['status_ids'] ?? []));
        $filters['owner_ids'] = array_values(array_filter($filters['owner_ids'] ?? []));

        if (array_key_exists('has_phone', $filters)) {
            $filters['has_phone'] = (bool) $filters['has_phone'];
        }

        if (empty($filters['search'])) {
            unset($filters['search']);
        }
        if (empty($filters['created_from'])) {
            unset($filters['created_from']);
        }
        if (empty($filters['created_to'])) {
            unset($filters['created_to']);
        }

        $headerType = $validated['header_type'] ?? 'none';
        $headerUrl = $headerType === 'none' ? null : Arr::get($validated, 'header_media_url');

        $campaign = Campaign::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'whatsapp_account_id' => $validated['account_id'],
            'whatsapp_template_id' => $validated['template_id'] ?? null,
            'template_name' => $validated['template_name'],
            'template_language' => $validated['template_language'],
            'header_type' => $headerType,
            'header_media_url' => $headerUrl,
            'wait_seconds' => $validated['wait_seconds'],
            'action_note' => $validated['action_note'],
            'max_recipients' => $validated['limit'] ?? null,
            'filters' => array_filter($filters),
            'settings' => [
                'source' => 'whatsapp_broadcast',
            ],
        ]);

        $bodyParameters = collect($validated['body_parameters'] ?? [])
            ->filter(function ($parameter) {
                return filled($parameter['source'] ?? null) || filled($parameter['fallback'] ?? null);
            })
            ->values();

        foreach ($bodyParameters as $index => $parameter) {
            CampaignMessage::create([
                'campaign_id' => $campaign->id,
                'component' => 'body',
                'sequence' => $index + 1,
                'source' => $parameter['source'] ?? null,
                'fallback' => $parameter['fallback'] ?? null,
                'text' => $parameter['fallback'] ?? null,
                'payload' => [
                    'source' => $parameter['source'] ?? null,
                    'fallback' => $parameter['fallback'] ?? null,
                ],
            ]);
        }

        return redirect()
            ->route('whatsapp-broadcasts.create')
            ->with('status', 'Campaña #' . $campaign->id . ' guardada. En el siguiente paso conectaremos el envío.');
    }
}
