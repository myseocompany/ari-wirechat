{{-- Buscador rápido --}}
<form action="/customers" method="GET" id="mini_filter_form" class="mb-3">
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
  'statuses' => $statuses ?? [],
  'users' => $users ?? [],
  'sources' => $sources ?? [],
])


{{-- Sección de filtros colapsable --}}
<div class="collapse" id="filterSection">
  <form action="/customers" method="GET" id="filter_form" class="card card-body border shadow-sm">



    {{-- INPUTS OCULTOS para que se envíen con el form --}}
  <input type="hidden" id="from_date" name="from_date" value="{{ $request->from_date }}">
  <input type="hidden" id="to_date" name="to_date" value="{{ $request->to_date }}">

  {{-- Selector visual --}}
  <div class="form-group">
    <label for="reportrange">Rango de fechas</label>
    <div id="reportrange" class="form-control" style="cursor: pointer; background: #fff;">
      <i class="fa fa-calendar"></i>&nbsp;
      <span>{{ $request->from_date && $request->to_date ? $request->from_date . ' - ' . $request->to_date : 'Seleccionar rango' }}</span>
      <i class="fa fa-caret-down float-right mt-1"></i>
    </div>
  </div>

  {{-- Enlaces rápidos --}}
  <div class="mb-2">
    <a href="#" class="btn btn-sm btn-light border" onclick="setRange(7)">Últimos 7 días</a>
    <a href="#" class="btn btn-sm btn-light border" onclick="setRange(10)">Últimos 10 días</a>
    <a href="#" class="btn btn-sm btn-light border" onclick="setRange(30)">Últimos 30 días</a>
    <a href="#" class="btn btn-sm btn-outline-secondary" onclick="clearRange()">Limpiar</a>
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
            <option value="{{ $item->iso2 }}" @selected($request->iso2 == $item->iso2)>
              {{ $item->name }}
            </option>
          @endforeach
        </select>
      </div>
      <div class="form-group col-md-6">
        <label for="status_id">Estado</label>
        <select name="status_id" class="form-control" id="status_id">
          <option value="">Todos</option>
          @foreach($statuses as $item)
            <option value="{{ $item->id }}" @selected($request->status_id == $item->id)>
              {{ $item->name }}
            </option>
          @endforeach
        </select>
      </div>
    </div>

    {{-- Usuario y fuente --}}
    <div class="form-row">
      <div class="form-group col-md-6">
        <label for="user_id">Usuario</label>
        <select name="user_id" class="form-control" id="user_id">
          <option value="">Todos</option>
          <option value="null" @selected($request->user_id === 'null')>Sin asignar</option>
          @foreach($users as $user)
            <option value="{{ $user->id }}" @selected($request->user_id == $user->id)>
              {{ Str::limit($user->name, 15) }}
            </option>
          @endforeach
        </select>
      </div>

      <div class="form-group col-md-6">
        <label for="source_id">Fuente</label>
        <select name="source_id" class="form-control" id="source_id">
          <option value="">Todas</option>
          @foreach($sources as $item)
            <option value="{{ $item->id }}" @selected($request->source_id == $item->id)>
              {{ Str::limit($item->name, 15) }}
            </option>
          @endforeach
        </select>
      </div>
    </div>

    {{-- Maker (tipo de cliente) --}}
    <div class="form-group">
      <label class="d-block">Tipo de cliente</label>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="maker" value="empty" id="maker_empty" onchange="submit();" @checked($request->maker === 'empty')>
        <label class="form-check-label" for="maker_empty">Sin clasificar</label>
      </div>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="maker" value="0" id="maker_0" onchange="submit();" @checked($request->maker === '0')>
        <label class="form-check-label" for="maker_0">Proyecto</label>
      </div>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="maker" value="1" id="maker_1" onchange="submit();" @checked($request->maker === '1')>
        <label class="form-check-label" for="maker_1">Hace empanadas</label>
      </div>
    </div>

    {{-- Fecha de creación o actualización --}}
    <div class="form-group">
      <label class="d-block">Filtrar por</label>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="created_updated" value="created" id="created" onchange="submit();" @checked(!isset($request->created_updated) || $request->created_updated === 'created')>
        <label class="form-check-label" for="created">Creado</label>
      </div>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="created_updated" value="updated" id="updated" onchange="submit();" @checked($request->created_updated === 'updated')>
        <label class="form-check-label" for="updated">Actualizado</label>
      </div>
    </div>

    <button type="submit" class="btn btn-primary mt-2">Aplicar filtros</button>

  </form>
</div>


