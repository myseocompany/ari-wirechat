<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Tag;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    /**
     * Display the dashboard KPIs with optional date filtering.
     */
    public function index(Request $request)
    {
        [$startDate, $endDate, $selectedRange, $customRange] = $this->resolveDateRange($request);

        $tagSlugs = [
            'mql' => 'Clientes MQL',
            'sql' => 'Clientes SQL',
            'converted' => 'Convertidos',
        ];

        $tags = Tag::whereIn('slug', array_keys($tagSlugs))->get();
        $tagsBySlug = $tags->keyBy(fn (Tag $tag) => strtolower($tag->slug ?? $tag->name ?? ''));
        $tagsById = $tags->keyBy('id');

        $customerQuery = Customer::query();
        if ($startDate && $endDate) {
            $customerQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        $metrics = [
            [
                'title' => 'Leads',
                'value' => $customerQuery->count(),
                'subtitle' => $this->buildSubtitle($selectedRange, 'Total de clientes registrados'),
                'color' => '#fff7ed',
                'accent' => '#f97316',
                'icon' => 'fa-users',
            ],
        ];

        foreach ($tagSlugs as $slug => $label) {
            $tag = $tagsBySlug->get($slug);
            $metrics[] = [
                'title' => $label,
                'value' => $this->countCustomersByTag($tag, $startDate, $endDate),
                'subtitle' => $tag?->description
                    ? $this->buildSubtitle($selectedRange, $tag->description)
                    : $this->buildSubtitle($selectedRange, "Clientes con la etiqueta {$label}."),
                'color' => $tag?->color ?: '#eef2ff',
                'accent' => $tag?->color ?: '#6366f1',
                'icon' => match ($slug) {
                    'mql' => 'fa-filter',
                    'sql' => 'fa-line-chart',
                    'converted' => 'fa-check-circle',
                    default => 'fa-tag',
                },
            ];
        }

        $fromDateValue = $request->input('from_date');
        $toDateValue = $request->input('to_date');

        if (! $fromDateValue && ! $toDateValue && $selectedRange !== 'all' && $startDate && $endDate) {
            $fromDateValue = $startDate->toDateString();
            $toDateValue = $endDate->toDateString();
        }

        $userBreakdown = $this->buildUserBreakdown($tagsById, $tagSlugs, $startDate, $endDate);

        return view('dashboard', [
            'metrics' => $metrics,
            'selectedRange' => $selectedRange,
            'customRange' => $customRange,
            'filterOptions' => $this->filterOptions(),
            'userBreakdown' => $userBreakdown,
            'hasUserBreakdown' => ! empty($userBreakdown),
            'fromDate' => $fromDateValue,
            'toDate' => $toDateValue,
        ]);
    }

    private function countCustomersByTag(?Tag $tag, ?CarbonInterface $startDate, ?CarbonInterface $endDate): int
    {
        if (! $tag) {
            return 0;
        }

        $query = $tag->customers();
        if ($startDate && $endDate) {
            $query->wherePivotBetween('customer_tag.created_at', [$startDate, $endDate]);
        }

        return $query->count();
    }

    private function resolveDateRange(Request $request): array
    {
        $range = $request->get('range', 'monthly');
        $now = now();
        $start = null;
        $end = null;
        $custom = [
            'start' => $request->input('from_date'),
            'end' => $request->input('to_date'),
        ];

        if ($custom['start'] && $custom['end']) {
            try {
                $start = Carbon::parse($custom['start'])->startOfDay();
                $end = Carbon::parse($custom['end'])->endOfDay();
                return [$start, $end, 'custom', $custom];
            } catch (\Throwable $e) {
                $start = $end = null;
                $custom = ['start' => null, 'end' => null];
            }
        }

        switch ($range) {
            case 'today':
                $start = $now->copy()->startOfDay();
                $end = $now->copy()->endOfDay();
                break;
            case 'yesterday':
                $start = $now->copy()->subDay()->startOfDay();
                $end = $now->copy()->subDay()->endOfDay();
                break;
            case 'weekly':
                $start = $now->copy()->startOfWeek();
                $end = $now->copy()->endOfWeek();
                break;
            case 'monthly':
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfMonth();
                break;
            case 'custom':
                if ($custom['start'] && $custom['end']) {
                    try {
                        $start = Carbon::parse($custom['start'])->startOfDay();
                        $end = Carbon::parse($custom['end'])->endOfDay();
                    } catch (\Throwable $e) {
                        $start = $end = null;
                    }
                }
                break;
            default:
                $range = 'all';
                $start = null;
                $end = null;
                break;
        }

        return [$start, $end, $range, $custom];
    }

    private function filterOptions(): array
    {
        return [
            'today' => 'Hoy',
            'yesterday' => 'Ayer',
            'weekly' => 'Semana',
            'monthly' => 'Mes',
            'all' => 'Todo',
        ];
    }

    private function buildSubtitle(string $range, string $text): string
    {
        return match ($range) {
            'today' => "{$text} - Hoy",
            'yesterday' => "{$text} - Ayer",
            'weekly' => "{$text} - Semana actual",
            'monthly' => "{$text} - Mes actual",
            'custom' => "{$text} - Rango seleccionado",
            default => $text,
        };
    }

    private function buildUserBreakdown(Collection $tagsById, array $tagLabels, ?CarbonInterface $start, ?CarbonInterface $end): array
    {
        if ($tagsById->isEmpty()) {
            return [];
        }

        $tagIds = $tagsById->keys()->all();
        $rows = Customer::query()
            ->join('customer_tag', 'customer_tag.customer_id', '=', 'customers.id')
            ->leftJoin('users', 'customers.user_id', '=', 'users.id')
            ->whereIn('customer_tag.tag_id', $tagIds)
            ->selectRaw('COALESCE(users.id, 0) as user_id')
            ->selectRaw('COALESCE(users.name, "Sin asignar") as user_name')
            ->selectRaw('customer_tag.tag_id as tag_id')
            ->selectRaw('COUNT(*) as total');

        if ($start && $end) {
            $rows->whereBetween('customer_tag.created_at', [$start, $end]);
        }

        $rows = $rows
            ->groupBy('user_id', 'user_name', 'customer_tag.tag_id')
            ->get();

        $grouped = [];
        foreach ($rows as $row) {
            $tag = $tagsById->get($row->tag_id);
            if (! $tag) {
                continue;
            }

            $slug = strtolower($tag->slug ?? $tag->name ?? '');
            if (! isset($grouped[$row->user_id])) {
                $grouped[$row->user_id] = [
                    'user_id' => $row->user_id,
                    'name' => $row->user_name,
                    'total' => 0,
                    'segments' => [],
                ];
            }

            $count = (int) $row->total;
            $grouped[$row->user_id]['segments'][] = [
                'slug' => $slug,
                'label' => $tagLabels[$slug] ?? $tag->name,
                'count' => $count,
                'color' => $tag->color ?: '#64748b',
            ];
            $grouped[$row->user_id]['total'] += $count;
        }

        return collect($grouped)
            ->sortByDesc('total')
            ->values()
            ->all();
    }
}
