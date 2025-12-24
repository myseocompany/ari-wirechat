@php
use App\Services\CustomerService;
@endphp

@include('customers.index_partials.active_filters', [
  'country_options' => $country_options ?? [],
  'statuses'        => $statuses ?? [],
  'users'           => $users ?? [],
  'sources'         => $sources ?? [],
  'tags'            => $tags ?? [],
])

<form action="/customers" method="GET" id="filter_form" class="flex flex-col gap-4">
  @if(request()->boolean('no_date'))
    <input type="hidden" name="no_date" value="1">
  @endif
  <input type="hidden" id="parent_status_id" name="parent_status_id" value="{{ $request->parent_status_id }}">

  <div class="flex flex-col gap-2">
    <label for="search" class="ds-mono text-xs uppercase tracking-[0.3em] text-slate-500">Buscar</label>
    <input
      type="text"
      id="search"
      name="search"
      class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-[color:var(--ds-coral)] focus:outline-none focus:ring-2 focus:ring-[color:var(--ds-blush)]"
      placeholder="Nombre, email, teléfono, empresa…"
      value="{{ $request->search ?? '' }}"
      autocomplete="off">
  </div>

  <input type="hidden" id="from_date" name="from_date" value="{{ $request->from_date }}">
  <input type="hidden" id="to_date" name="to_date" value="{{ $request->to_date }}">

  <div class="flex flex-col gap-2">
    <label for="reportrange_input" class="ds-mono text-xs uppercase tracking-[0.3em] text-slate-500">Rango de fechas</label>
    <div class="flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-3 py-2 shadow-sm">
      <input
        type="text"
        id="reportrange_input"
        class="w-full text-sm text-slate-700 focus:outline-none"
        placeholder="Seleccionar rango"
        value="{{ ($request->from_date && $request->to_date) ? $request->from_date.' - '.$request->to_date : '' }}"
        autocomplete="off">
      <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[color:var(--ds-cloud)] text-[color:var(--ds-navy)]">
        <i class="fa fa-calendar"></i>
      </span>
    </div>

    <div class="flex flex-wrap gap-2">
      <a href="#" class="rounded-full border border-slate-200 bg-white px-3 py-2 text-[0.7rem] font-semibold text-slate-600" onclick="return setRange(7)">Últimos 7 días</a>
      <a href="#" class="rounded-full border border-slate-200 bg-white px-3 py-2 text-[0.7rem] font-semibold text-slate-600" onclick="return setRange(10)">Últimos 10 días</a>
      <a href="#" class="rounded-full border border-slate-200 bg-white px-3 py-2 text-[0.7rem] font-semibold text-slate-600" onclick="return setRange(30)">Últimos 30 días</a>
      <a href="#" class="rounded-full border border-slate-200 bg-white px-3 py-2 text-[0.7rem] font-semibold text-slate-600" onclick="return setThisWeek()">Esta semana</a>
      <a href="#" class="rounded-full border border-slate-200 bg-white px-3 py-2 text-[0.7rem] font-semibold text-slate-600" onclick="return setLastWeek()">Semana pasada</a>
      <a href="#" class="rounded-full border border-slate-200 bg-white px-3 py-2 text-[0.7rem] font-semibold text-slate-600" onclick="return setCurrentMonth()">Este mes</a>
      <a href="#" class="rounded-full border border-slate-200 bg-white px-3 py-2 text-[0.7rem] font-semibold text-slate-600" onclick="return setLastMonth()">Mes pasado</a>
      <a href="#" class="rounded-full border border-[color:var(--ds-coral)] px-3 py-2 text-[0.7rem] font-semibold text-[color:var(--ds-coral)]" onclick="return clearRange()">Limpiar</a>
    </div>
  </div>

  <div class="grid gap-4 sm:grid-cols-2">
    <div class="flex flex-col gap-2">
      <label for="scoring_profile" class="ds-mono text-xs uppercase tracking-[0.3em] text-slate-500">Perfil</label>
      <select name="scoring_profile" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-[color:var(--ds-coral)] focus:outline-none focus:ring-2 focus:ring-[color:var(--ds-blush)]" id="scoring_profile">
        <option value="">Todos</option>
        <option value="a" @selected($request->scoring_profile == 'a')>★★★★</option>
        <option value="b" @selected($request->scoring_profile == 'b')>★★★</option>
        <option value="c" @selected($request->scoring_profile == 'c')>★★</option>
        <option value="d" @selected($request->scoring_profile == 'd')>★</option>
      </select>
    </div>
    <div class="flex flex-col gap-2">
      <label for="scoring_interest" class="ds-mono text-xs uppercase tracking-[0.3em] text-slate-500">Interés</label>
      <select name="scoring_interest" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-[color:var(--ds-coral)] focus:outline-none focus:ring-2 focus:ring-[color:var(--ds-blush)]" id="scoring_interest">
        <option value="" {{ request()->has('scoring_interest') ? '' : 'selected' }}>Todos</option>
        @foreach($scoring_interest as $item)
          <option value="{{ $item->scoring_interest }}"
            {{ (string)request()->query('scoring_interest', '') === (string)$item->scoring_interest ? 'selected' : '' }}>
            {{ $item->scoring_interest }}
          </option>
        @endforeach
      </select>
    </div>
  </div>

  <div class="grid gap-4 sm:grid-cols-2">
    <div class="flex flex-col gap-2">
      <label for="country" class="ds-mono text-xs uppercase tracking-[0.3em] text-slate-500">País</label>
      <select name="country" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-[color:var(--ds-coral)] focus:outline-none focus:ring-2 focus:ring-[color:var(--ds-blush)]" id="country">
        <option value="">Todos</option>
        @foreach($country_options as $item)
          <option value="{{ $item->iso2 }}" @selected($request->country == $item->iso2)>{{ $item->name }}</option>
        @endforeach
      </select>
    </div>
    <div class="flex flex-col gap-2">
      <label for="status_id" class="ds-mono text-xs uppercase tracking-[0.3em] text-slate-500">Estado</label>
      <select name="status_id" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-[color:var(--ds-coral)] focus:outline-none focus:ring-2 focus:ring-[color:var(--ds-blush)]" id="status_id">
        <option value="">Todos</option>
        <option value="{{ CustomerService::STATUS_FILTER_UNASSIGNED }}" @selected($request->status_id === CustomerService::STATUS_FILTER_UNASSIGNED)>Sin estado</option>
        @foreach($statuses as $item)
          <option value="{{ $item->id }}" @selected($request->status_id == $item->id)>{{ $item->name }}</option>
        @endforeach
      </select>
    </div>
  </div>

  <div class="grid gap-4 sm:grid-cols-2">
    @php $canViewAll = auth()->user()?->canViewAllCustomers() ?? false; @endphp
    <div class="flex flex-col gap-2">
      <label for="user_id" class="ds-mono text-xs uppercase tracking-[0.3em] text-slate-500">Usuario</label>
      @if($canViewAll)
        <select name="user_id" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-[color:var(--ds-coral)] focus:outline-none focus:ring-2 focus:ring-[color:var(--ds-blush)]" id="user_id">
          <option value="">Todos</option>
          <option value="null" @selected($request->user_id === 'null')>Sin asignar</option>
          @foreach($users as $user)
            <option value="{{ $user->id }}" @selected($request->user_id == $user->id)>{{ Str::limit($user->name, 15) }}</option>
          @endforeach
        </select>
      @else
        <div class="rounded-xl border border-slate-200 bg-[color:var(--ds-cloud)] px-3 py-2 text-sm text-slate-500">Solo tus clientes</div>
      @endif
    </div>
    <div class="flex flex-col gap-2">
      <label for="source_id" class="ds-mono text-xs uppercase tracking-[0.3em] text-slate-500">Fuente</label>
      <select name="source_id" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-[color:var(--ds-coral)] focus:outline-none focus:ring-2 focus:ring-[color:var(--ds-blush)]" id="source_id">
        <option value="">Todas</option>
        @foreach($sources as $item)
          <option value="{{ $item->id }}" @selected($request->source_id == $item->id)>{{ Str::limit($item->name, 15) }}</option>
        @endforeach
      </select>
    </div>
  </div>

  <div class="flex flex-col gap-2">
    <label for="tag_id" class="ds-mono text-xs uppercase tracking-[0.3em] text-slate-500">Etiqueta</label>
    <select name="tag_id" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-[color:var(--ds-coral)] focus:outline-none focus:ring-2 focus:ring-[color:var(--ds-blush)]" id="tag_id">
      <option value="">Todas</option>
      @foreach($tags as $tag)
        <option value="{{ $tag->id }}" @selected($request->tag_id == $tag->id)>{{ $tag->name }}</option>
      @endforeach
    </select>
  </div>

  <div class="flex flex-col gap-2">
    <span class="ds-mono text-xs uppercase tracking-[0.3em] text-slate-500">Con cotización</span>
    <div class="flex flex-wrap gap-3">
      <label class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600">
        <input class="text-[color:var(--ds-coral)] focus:ring-[color:var(--ds-coral)]" type="radio" name="has_quote" value="1" onchange="this.form.submit();" @checked(request('has_quote') === '1')>
        Sí
      </label>
      <label class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600">
        <input class="text-[color:var(--ds-coral)] focus:ring-[color:var(--ds-coral)]" type="radio" name="has_quote" value="0" onchange="this.form.submit();" @checked(request('has_quote') === '0')>
        No
      </label>
    </div>
  </div>

  <div class="flex flex-col gap-2">
    <span class="ds-mono text-xs uppercase tracking-[0.3em] text-slate-500">Tipo de cliente</span>
    <div class="flex flex-wrap gap-3">
      <label class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600">
        <input class="text-[color:var(--ds-coral)] focus:ring-[color:var(--ds-coral)]" type="radio" name="maker" value="empty" onchange="this.form.submit();" @checked($request->maker === 'empty')>
        Sin clasificar
      </label>
      <label class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600">
        <input class="text-[color:var(--ds-coral)] focus:ring-[color:var(--ds-coral)]" type="radio" name="maker" value="0" onchange="this.form.submit();" @checked($request->maker === '0')>
        Proyecto
      </label>
      <label class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600">
        <input class="text-[color:var(--ds-coral)] focus:ring-[color:var(--ds-coral)]" type="radio" name="maker" value="1" onchange="this.form.submit();" @checked($request->maker === '1')>
        Hace empanadas
      </label>
    </div>
  </div>

  <div class="flex flex-col gap-2">
    <span class="ds-mono text-xs uppercase tracking-[0.3em] text-slate-500">Filtrar por</span>
    <div class="flex flex-wrap gap-3">
      <label class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600">
        <input class="text-[color:var(--ds-coral)] focus:ring-[color:var(--ds-coral)]" type="radio" name="created_updated" value="created" onchange="this.form.submit();" @checked(!isset($request->created_updated) || $request->created_updated === 'created')>
        Creado
      </label>
      <label class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600">
        <input class="text-[color:var(--ds-coral)] focus:ring-[color:var(--ds-coral)]" type="radio" name="created_updated" value="updated" onchange="this.form.submit();" @checked($request->created_updated === 'updated')>
        Actualizado
      </label>
    </div>
  </div>

  <button type="submit" class="rounded-xl bg-[color:var(--ds-coral)] px-4 py-3 text-sm font-semibold text-white shadow-[0_12px_24px_rgba(255,92,92,0.35)]">Aplicar filtros</button>
</form>
