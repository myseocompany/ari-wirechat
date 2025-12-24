@php
use App\Services\CustomerService;
use Carbon\Carbon;
use Illuminate\Support\Arr;

$q = Arr::where(
  Arr::except(request()->all(), ['page']),
  fn ($value) => $value !== null && $value !== ''
);

$parent_statuses = $parent_statuses ?? collect();

$perfil = [
  'a' => '★★★★', 'b' => '★★★', 'c' => '★★', 'd' => '★'
];
$makerMap = ['empty' => 'Sin clasificar', '0' => 'Proyecto', '1' => 'Hace empanadas'];
$createdUpdatedMap = ['created' => 'Creado', 'updated' => 'Actualizado'];

$countryName = function($iso2) use ($country_options) {
  if (empty($iso2) || empty($country_options)) return $iso2;
  $item = collect($country_options)->firstWhere('iso2', $iso2);
  return $item->name ?? $iso2;
};
$tagName = function($id) use ($tags) {
  if (empty($id) || empty($tags)) return $id;
  $item = collect($tags)->firstWhere('id', (int)$id);
  return $item->name ?? $id;
};
$statusName = function($id) use ($statuses) {
  if ($id === CustomerService::STATUS_FILTER_UNASSIGNED) {
    return 'Sin estado';
  }
  if (empty($id) || empty($statuses)) return $id;
  $item = collect($statuses)->firstWhere('id', (int)$id);
  return $item->name ?? $id;
};
$userName = function($id) use ($users) {
  if ($id === 'null') return 'Sin asignar';
  if (empty($id) || empty($users)) return $id;
  $item = collect($users)->firstWhere('id', (int)$id);
  return $item->name ?? $id;
};
$sourceName = function($id) use ($sources) {
  if (empty($id) || empty($sources)) return $id;
  $item = collect($sources)->firstWhere('id', (int)$id);
  return $item->name ?? $id;
};
$parentName = function($id) use ($parent_statuses) {
  if (empty($id) || empty($parent_statuses)) return $id;
  $item = collect($parent_statuses)->firstWhere('id', (int)$id);
  return $item->name ?? $id;
};

$hasDateInputs = !empty($q['from_date'] ?? null) || !empty($q['to_date'] ?? null);
$skipDefaultDateRange = !empty($q['no_date'] ?? null);
$showDefaultDateRange = !empty($q) && !$hasDateInputs && empty($q['search'] ?? null) && ! $skipDefaultDateRange;
$defaultFrom = Carbon::today()->subDay()->setTime(17, 0);
$defaultTo = Carbon::today()->endOfDay();
$displayFrom = $hasDateInputs
  ? ($q['from_date'] ?? '1900-01-01')
  : $defaultFrom->format('Y-m-d H:i');
$displayTo = $hasDateInputs
  ? ($q['to_date'] ?? Carbon::today()->format('Y-m-d'))
  : $defaultTo->format('Y-m-d H:i');

$hasAny = !empty($q);
@endphp

@if($hasAny)
  <div class="rounded-2xl border border-slate-200 bg-[color:var(--ds-cloud)] p-4 shadow-sm">
    <div class="flex flex-wrap items-center gap-2">
      <span class="ds-mono text-xs uppercase tracking-[0.3em] text-slate-500">Filtros activos</span>

      @if(array_key_exists('scoring_profile', $q))
        <span class="inline-flex items-center gap-2 rounded-full border border-white/70 bg-white/80 px-3 py-1 text-[0.7rem] font-semibold text-slate-600">
          Perfil: {{ $perfil[$q['scoring_profile']] ?? $q['scoring_profile'] }}
          <a class="text-[color:var(--ds-coral)]" href="{{ url()->current() . '?' . http_build_query(Arr::except($q, ['scoring_profile'])) }}">×</a>
        </span>
      @endif

      @if(array_key_exists('scoring_interest', $q))
        <span class="inline-flex items-center gap-2 rounded-full border border-white/70 bg-white/80 px-3 py-1 text-[0.7rem] font-semibold text-slate-600">
          Interés: {{ $q['scoring_interest'] }}
          <a class="text-[color:var(--ds-coral)]" href="{{ url()->current() . '?' . http_build_query(Arr::except($q, ['scoring_interest'])) }}">×</a>
        </span>
      @endif

      @if(array_key_exists('status_id', $q))
        <span class="inline-flex items-center gap-2 rounded-full border border-white/70 bg-white/80 px-3 py-1 text-[0.7rem] font-semibold text-slate-600">
          Estado: {{ $statusName($q['status_id']) }}
          <a class="text-[color:var(--ds-coral)]" href="{{ url()->current() . '?' . http_build_query(Arr::except($q, ['status_id'])) }}">×</a>
        </span>
      @endif

      @if(array_key_exists('parent_status_id', $q))
        <span class="inline-flex items-center gap-2 rounded-full border border-white/70 bg-white/80 px-3 py-1 text-[0.7rem] font-semibold text-slate-600">
          Macro estado: {{ $parentName($q['parent_status_id']) }}
          <a class="text-[color:var(--ds-coral)]" href="{{ url()->current() . '?' . http_build_query(Arr::except($q, ['parent_status_id'])) }}">×</a>
        </span>
      @endif

      @if(array_key_exists('created_updated', $q))
        <span class="inline-flex items-center gap-2 rounded-full border border-white/70 bg-white/80 px-3 py-1 text-[0.7rem] font-semibold text-slate-600">
          Fecha en: {{ $createdUpdatedMap[$q['created_updated']] ?? $q['created_updated'] }}
          <a class="text-[color:var(--ds-coral)]" href="{{ url()->current() . '?' . http_build_query(Arr::except($q, ['created_updated'])) }}">×</a>
        </span>
      @endif

      @if($hasDateInputs || $showDefaultDateRange || ($skipDefaultDateRange ?? false))
        <span class="inline-flex items-center gap-2 rounded-full border border-white/70 bg-white/80 px-3 py-1 text-[0.7rem] font-semibold text-slate-600">
          @if($skipDefaultDateRange ?? false)
            Rango: Todos
            <a class="text-[color:var(--ds-coral)]" href="{{ url()->current() . '?' . http_build_query(Arr::except($q, ['no_date'])) }}">×</a>
          @else
            Rango: {{ $displayFrom }} - {{ $displayTo }}
            <a class="text-[color:var(--ds-coral)]" href="{{ url()->current() . '?' . http_build_query(
              $hasDateInputs
                ? Arr::except($q, ['from_date', 'to_date', 'no_date'])
                : array_merge(Arr::except($q, ['from_date', 'to_date']), ['no_date' => 1])
            ) }}">×</a>
          @endif
        </span>
      @endif

      @if(array_key_exists('user_id', $q))
        <span class="inline-flex items-center gap-2 rounded-full border border-white/70 bg-white/80 px-3 py-1 text-[0.7rem] font-semibold text-slate-600">
          Usuario: {{ $userName($q['user_id']) }}
          <a class="text-[color:var(--ds-coral)]" href="{{ url()->current() . '?' . http_build_query(Arr::except($q, ['user_id'])) }}">×</a>
        </span>
      @endif

      @if(array_key_exists('tag_id', $q))
        <span class="inline-flex items-center gap-2 rounded-full border border-white/70 bg-white/80 px-3 py-1 text-[0.7rem] font-semibold text-slate-600">
          Etiqueta: {{ $tagName($q['tag_id']) }}
          <a class="text-[color:var(--ds-coral)]" href="{{ url()->current() . '?' . http_build_query(Arr::except($q, ['tag_id'])) }}">×</a>
        </span>
      @endif

      @if(array_key_exists('has_quote', $q))
        <span class="inline-flex items-center gap-2 rounded-full border border-white/70 bg-white/80 px-3 py-1 text-[0.7rem] font-semibold text-slate-600">
          Cotización: {{ $q['has_quote'] === '1' ? 'Sí' : 'No' }}
          <a class="text-[color:var(--ds-coral)]" href="{{ url()->current() . '?' . http_build_query(Arr::except($q, ['has_quote'])) }}">×</a>
        </span>
      @endif
    </div>

    @if(!empty($q))
      <a class="mt-3 inline-flex items-center rounded-full border border-white/70 bg-white px-4 py-2 text-xs font-semibold text-[color:var(--ds-navy)] shadow-sm" href="{{ url()->current() }}">Limpiar todo</a>
    @endif
  </div>
@endif
