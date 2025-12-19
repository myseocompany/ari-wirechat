{{-- ========================= --}}
{{--  FILTRO COMPLETO CLIENTES  --}}
{{-- ========================= --}}

{{-- Buscador rápido --}}
<form action="/customers" method="GET" id="mini_filter_form" class="mb-3">
  @if(request()->boolean('no_date'))
    <input type="hidden" name="no_date" value="1">
  @endif
  <div class="input-group">
    <input type="text" name="search" id="search" class="form-control" placeholder="Buscar..." value="{{ $request->search ?? '' }}">
    <div class="input-group-append">
      <button class="btn btn-primary" type="submit">Buscar</button>
    </div>
  </div>
</form>

{{-- Botón para mostrar filtros avanzados --}}
<button class="btn btn-link text-primary mb-2" type="button" data-toggle="collapse" data-target="#filterSection" aria-expanded="false" aria-controls="filterSection">
  Filtros avanzados
</button>

@include('customers.index_partials.active_filters', [
  'country_options' => $country_options ?? [],
  'statuses'        => $statuses ?? [],
  'users'           => $users ?? [],
  'sources'         => $sources ?? [],
  'tags'            => $tags ?? [],
])

{{-- Sección de filtros colapsable --}}
<div class="collapse" id="filterSection">
  <form action="/customers" method="GET" id="filter_form" class="card card-body border shadow-sm">
    @if(request()->boolean('no_date'))
      <input type="hidden" name="no_date" value="1">
    @endif
    <div class="form-group">
  <label for="search_adv" class="mb-1">Buscar</label>
  <input
    type="text"
    id="search_adv"
    name="search"
    class="form-control"
    placeholder="Nombre, email, teléfono, empresa…"
    value="{{ $request->search ?? '' }}"
    autocomplete="off">
</div>
    {{-- HIDDEN para envío real al backend --}}
    <input type="hidden" id="from_date" name="from_date" value="{{ $request->from_date }}">
    <input type="hidden" id="to_date"   name="to_date"   value="{{ $request->to_date }}">

    {{-- Selector visual (Date Range Picker) --}}
    <div class="form-group">
      <label for="reportrange_input" class="mb-1">Rango de fechas</label>

      {{-- Input editable + calendario (editable true) --}}
      <div class="input-group">
        <input
          type="text"
          id="reportrange_input"
          class="form-control"
          placeholder="Seleccionar rango"
          value="{{ ($request->from_date && $request->to_date) ? $request->from_date.' - '.$request->to_date : '' }}"
          autocomplete="off">
        <div class="input-group-append">
          <span class="input-group-text"><i class="fa fa-calendar"></i></span>
        </div>
      </div>

      {{-- Enlaces rápidos --}}
      <div class="mt-2">
        <a href="#" class="btn btn-sm btn-light border" onclick="return setRange(7)">Últimos 7 días</a>
        <a href="#" class="btn btn-sm btn-light border" onclick="return setRange(10)">Últimos 10 días</a>
        <a href="#" class="btn btn-sm btn-light border" onclick="return setRange(30)">Últimos 30 días</a>
        <a href="#" class="btn btn-sm btn-light border" onclick="return setThisWeek()">Esta semana</a>
        <a href="#" class="btn btn-sm btn-light border" onclick="return setLastWeek()">Semana pasada</a>
        <a href="#" class="btn btn-sm btn-light border" onclick="return setCurrentMonth()">Este mes</a>
        <a href="#" class="btn btn-sm btn-light border" onclick="return setLastMonth()">Mes pasado</a>
        <a href="#" class="btn btn-sm btn-outline-secondary" onclick="return clearRange()">Limpiar</a>
      </div>
    </div>

    {{-- Perfil e interés --}}
    <div class="form-row">
      <div class="form-group col-md-6">
        <label for="scoring_profile">Perfil</label>
        <select name="scoring_profile" class="form-control" id="scoring_profile">
          <option value="">Todos</option>
          <option value="a" @selected($request->scoring_profile == 'a')>★★★★</option>
          <option value="b" @selected($request->scoring_profile == 'b')>★★★</option>
          <option value="c" @selected($request->scoring_profile == 'c')>★★</option>
          <option value="d" @selected($request->scoring_profile == 'd')>★</option>
        </select>
      </div>
      <div class="form-group col-md-6">
        <label for="scoring_interest">Interés</label>
        <select name="scoring_interest" class="form-control" id="scoring_interest">
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

    {{-- País y estado --}}
    <div class="form-row">
      <div class="form-group col-md-6">
        <label for="country">País</label>
        <select name="country" class="form-control" id="country">
          <option value="">Todos</option>
          @foreach($country_options as $item)
            <option value="{{ $item->iso2 }}" @selected($request->country == $item->iso2)>{{ $item->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group col-md-6">
        <label for="status_id">Estado</label>
        <select name="status_id" class="form-control" id="status_id">
          <option value="">Todos</option>
          @foreach($statuses as $item)
            <option value="{{ $item->id }}" @selected($request->status_id == $item->id)>{{ $item->name }}</option>
          @endforeach
        </select>
      </div>
    </div>

    {{-- Usuario y fuente --}}
    <div class="form-row">
      @php $canViewAll = auth()->user()?->canViewAllCustomers() ?? false; @endphp
      <div class="form-group col-md-6">
        <label for="user_id">Usuario</label>
        @if($canViewAll)
          <select name="user_id" class="form-control" id="user_id">
            <option value="">Todos</option>
            <option value="null" @selected($request->user_id === 'null')>Sin asignar</option>
            @foreach($users as $user)
              <option value="{{ $user->id }}" @selected($request->user_id == $user->id)>{{ Str::limit($user->name, 15) }}</option>
            @endforeach
          </select>
        @else
          <div class="form-control bg-light text-muted">Solo tus clientes</div>
        @endif
      </div>
      <div class="form-group col-md-6">
        <label for="source_id">Fuente</label>
        <select name="source_id" class="form-control" id="source_id">
          <option value="">Todas</option>
          @foreach($sources as $item)
            <option value="{{ $item->id }}" @selected($request->source_id == $item->id)>{{ Str::limit($item->name, 15) }}</option>
          @endforeach
        </select>
      </div>
    </div>

    <div class="form-group">
      <label for="tag_id">Etiqueta</label>
      <select name="tag_id" class="form-control" id="tag_id">
        <option value="">Todas</option>
        @foreach($tags as $tag)
          <option value="{{ $tag->id }}" @selected($request->tag_id == $tag->id)>{{ $tag->name }}</option>
        @endforeach
      </select>
    </div>

    {{-- Tiene cotización --}}
    <div class="form-group">
      <label class="d-block">Con Cotización</label>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="has_quote" value="1" id="has_quote_1"
          onchange="this.form.submit();" @checked(request('has_quote') === '1')>
        <label class="form-check-label" for="has_quote_1">Sí</label>
      </div>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="has_quote" value="0" id="has_quote_0"
          onchange="this.form.submit();" @checked(request('has_quote') === '0')>
        <label class="form-check-label" for="has_quote_0">No</label>
      </div>
    </div>

    {{-- Maker (tipo de cliente) --}}
    <div class="form-group">
      <label class="d-block">Tipo de cliente</label>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="maker" value="empty" id="maker_empty" onchange="this.form.submit();" @checked($request->maker === 'empty')>
        <label class="form-check-label" for="maker_empty">Sin clasificar</label>
      </div>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="maker" value="0" id="maker_0" onchange="this.form.submit();" @checked($request->maker === '0')>
        <label class="form-check-label" for="maker_0">Proyecto</label>
      </div>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="maker" value="1" id="maker_1" onchange="this.form.submit();" @checked($request->maker === '1')>
        <label class="form-check-label" for="maker_1">Hace empanadas</label>
      </div>
    </div>

    {{-- Fecha de creación o actualización --}}
    <div class="form-group">
      <label class="d-block">Filtrar por</label>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="created_updated" value="created" id="created" onchange="this.form.submit();" @checked(!isset($request->created_updated) || $request->created_updated === 'created')>
        <label class="form-check-label" for="created">Creado</label>
      </div>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="created_updated" value="updated" id="updated" onchange="this.form.submit();" @checked($request->created_updated === 'updated')>
        <label class="form-check-label" for="updated">Actualizado</label>
      </div>
    </div>

    <button type="submit" class="btn btn-primary mt-2">Aplicar filtros</button>
  </form>
</div>
