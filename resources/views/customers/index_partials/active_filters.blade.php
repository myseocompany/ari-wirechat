@php
use Carbon\Carbon;
use Illuminate\Support\Arr;

$q = Arr::where(
  request()->all(),
  fn ($value) => $value !== null && $value !== ''
);

/** Helpers de valor legible */
$perfil = [
  'a' => '★★★★', 'b' => '★★★', 'c' => '★★', 'd' => '★'
];
$makerMap = ['empty' => 'Sin clasificar', '0' => 'Proyecto', '1' => 'Hace empanadas'];
$createdUpdatedMap = ['created' => 'Creado', 'updated' => 'Actualizado'];

/** Lookup helpers (si los pasas desde el controlador a la vista) */
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

$hasDateInputs = !empty($q['from_date'] ?? null) || !empty($q['to_date'] ?? null);
$skipDefaultDateRange = !empty($q['no_date'] ?? null);
$showDefaultDateRange = !$hasDateInputs && empty($q['search'] ?? null) && ! $skipDefaultDateRange;
$defaultFrom = Carbon::today()->subDay()->setTime(17, 0);
$defaultTo = Carbon::today()->endOfDay();
$displayFrom = $hasDateInputs
  ? ($q['from_date'] ?? '1900-01-01')
  : $defaultFrom->format('Y-m-d H:i');
$displayTo = $hasDateInputs
  ? ($q['to_date'] ?? Carbon::today()->format('Y-m-d'))
  : $defaultTo->format('Y-m-d H:i');

/** ¿Hay algo que mostrar? */
$hasAny =
  ($q['search'] ?? null) ||
  ($q['from_date'] ?? null) || ($q['to_date'] ?? null) || $showDefaultDateRange ||
  ($q['scoring_profile'] ?? null) || ($q['scoring_interest'] ?? null) ||
  ($q['country'] ?? null) || ($q['status_id'] ?? null) ||
  ($q['user_id'] ?? null) || ($q['source_id'] ?? null) || ($q['tag_id'] ?? null) ||
  ($q['maker'] ?? null) || ($q['created_updated'] ?? null) ||
  ($q['has_quote'] ?? null) || $skipDefaultDateRange;
@endphp

@if($hasAny)

<div class="mb-2">
  <strong>Filtros activos</strong>

  {{-- Perfil --}}
  @if(array_key_exists('scoring_profile', $q))
    @php $perfil = ['a' => '★★★★','b' => '★★★','c' => '★★','d' => '★']; @endphp
    <span class="badge badge-pill badge-light border text-danger ml-2">
      Perfil: {{ $perfil[$q['scoring_profile']] ?? $q['scoring_profile'] }}
      <a class="ml-1 text-danger"
         href="{{ url()->current() . '?' . http_build_query(Arr::except($q, ['scoring_profile'])) }}">×</a>
    </span>
  @endif

  {{-- Interés (ojo: permitir '0' explícito, pero no mostrar si la clave no existe) --}}
  @if(array_key_exists('scoring_interest', $q))
    <span class="badge badge-pill badge-light border text-danger ml-2">
      Interés: {{ $q['scoring_interest'] }}
      <a class="ml-1 text-danger"
         href="{{ url()->current() . '?' . http_build_query(Arr::except($q, ['scoring_interest'])) }}">×</a>
    </span>
  @endif

  {{-- Estado --}}
  @if(array_key_exists('status_id', $q))
    <span class="badge badge-pill badge-light border text-danger ml-2">
      Estado: {{ $statuses->firstWhere('id', $q['status_id'])->name ?? $q['status_id'] }}
      <a class="ml-1 text-danger"
         href="{{ url()->current() . '?' . http_build_query(Arr::except($q, ['status_id'])) }}">×</a>
    </span>
  @endif

  {{-- Creado/Actualizado --}}
  @if(array_key_exists('created_updated', $q))
    <span class="badge badge-pill badge-light border text-danger ml-2">
      Fecha en: {{ $q['created_updated'] === 'created' ? 'Creado' : 'Actualizado' }}
      <a class="ml-1 text-danger"
         href="{{ url()->current() . '?' . http_build_query(Arr::except($q, ['created_updated'])) }}">×</a>
    </span>
  @endif

  {{-- Rango de fechas --}}
  @if($hasDateInputs || $showDefaultDateRange || $skipDefaultDateRange)
    <span class="badge badge-pill badge-light border text-danger ml-2">
      @if($skipDefaultDateRange)
        Rango: Todos
        <a class="ml-1 text-danger"
          href="{{ url()->current() . '?' . http_build_query(Arr::except($q, ['no_date'])) }}">×</a>
      @else
        Rango: {{ $displayFrom }} - {{ $displayTo }}
        <a class="ml-1 text-danger"
          href="{{ url()->current() . '?' . http_build_query(
            $hasDateInputs
              ? Arr::except($q, ['from_date', 'to_date', 'no_date'])
              : array_merge(Arr::except($q, ['from_date', 'to_date']), ['no_date' => 1])
          ) }}">×</a>
      @endif
    </span>
  @endif

  {{-- Usuario --}}
  @if(array_key_exists('user_id', $q))
    <span class="badge badge-pill badge-light border text-danger ml-2">
      Usuario: {{ $userName($q['user_id']) }}
      <a class="ml-1 text-danger"
        href="{{ url()->current() . '?' . http_build_query(Arr::except($q, ['user_id'])) }}">×</a>
    </span>
  @endif

  {{-- Etiqueta --}}
  @if(array_key_exists('tag_id', $q))
    <span class="badge badge-pill badge-light border text-danger ml-2">
      Etiqueta: {{ $tagName($q['tag_id']) }}
      <a class="ml-1 text-danger"
         href="{{ url()->current() . '?' . http_build_query(Arr::except($q, ['tag_id'])) }}">×</a>
    </span>
  @endif

  {{-- Cotización --}}
  @if(array_key_exists('has_quote', $q))
    <span class="badge badge-pill badge-light border text-danger ml-2">
      Cotización: {{ $q['has_quote'] === '1' ? 'Sí' : 'No' }}
      <a class="ml-1 text-danger"
        href="{{ url()->current() . '?' . http_build_query(Arr::except($q, ['has_quote'])) }}">×</a>
    </span>
  @endif

  {{-- Botón limpiar todo si hay algo --}}
  @if(!empty($q))
    <a class="btn btn-sm btn-outline-secondary ml-2" href="{{ url()->current() }}">Limpiar todo</a>
  @endif
</div>
@endif
