@extends('layout')

@section('content')
<div style="overflow-x:auto;">
  <form class="form" action="{{ route('optimizer.merge') }}" method="POST">
    @csrf

    {{-- IDs candidatos --}}
    @foreach($model as $item)
      <input type="hidden" name="customer_id_all[]" value="{{ $item->id }}">
    @endforeach

    @php
      // Campos editables
      $fields = [
        'status_id','user_id','source_id','product_id',
        'name','document','position','business',
        'phone','phone2','contact_phone2','phone_wp','total_sold',
        'email','address','city','country','department',
        'contact_name','contact_email','contact_position',
        'purchase_date','notes','technical_visit','gender',
        'scoring_interest','scoring_profile','rd_public_url','src','cid','vas',
        'rd_source','country2','count_empanadas',
      ];
      $first = $model->first(); // registro base para valores iniciales
    @endphp

    <table class="table table-striped">
      {{-- Selección de registro ganador --}}
      <tr>
        <td>Registros</td>
        <td>Editar</td>
        @foreach($model as $i => $item)
          <td>
            <label>
              <input type="radio" name="customer_id" value="{{ $item->id }}" {{ $i===0 ? 'checked' : '' }}>
              <a href="{{ url("/customers/{$item->id}/show") }}">{{ $item->id }}</a>
            </label>
          </td>
        @endforeach
      </tr>

      {{-- Filas por cada campo --}}
      @foreach($fields as $field)
        @php $initial = data_get($first, $field); @endphp
        <tr>
          <td><strong>{{ $field }}:</strong></td>

          {{-- Editor principal con valor inicial --}}
          <td>
            @switch($field)
              @case('status_id')
                <select name="status_id" id="status_id" class="form-control" style="width:200px">
                  <option value="">Seleccione un estado</option>
                  @foreach($statuses_options as $opt)
                    <option value="{{ $opt->id }}" {{ (string)$opt->id === (string)$initial ? 'selected' : '' }}>
                      {{ $opt->name }}
                    </option>
                  @endforeach
                </select>
                @break

              @case('user_id')
                <select name="user_id" id="user_id" class="form-control" style="width:200px">
                  <option value="">Seleccione un usuario</option>
                  @foreach($user as $u)
                    <option value="{{ $u->id }}" {{ (string)$u->id === (string)$initial ? 'selected' : '' }}>
                      {{ $u->name }}-{{ $u->id }}
                    </option>
                  @endforeach
                </select>
                @break

              @case('source_id')
                <select name="source_id" id="source_id" class="form-control" style="width:200px">
                  <option value="">Seleccione una fuente</option>
                  @foreach($customers_source as $src)
                    <option value="{{ $src->id }}" {{ (string)$src->id === (string)$initial ? 'selected' : '' }}>
                      {{ $src->name }}
                    </option>
                  @endforeach
                </select>
                @break

              @case('product_id')
                <select name="product_id" id="product_id" class="form-control" style="width:200px">
                  <option value="">Seleccione un producto</option>
                  @foreach($products as $p)
                    <option value="{{ $p->id }}" {{ (string)$p->id === (string)$initial ? 'selected' : '' }}>
                      {{ $p->name ?? ('#'.$p->id) }}
                    </option>
                  @endforeach
                </select>
                @break

              @case('purchase_date')
                <input name="purchase_date" id="purchase_date" type="date" class="form-control" style="width:200px"
                       value="{{ $initial ? \Illuminate\Support\Str::of($initial)->substr(0,10) : '' }}">
                @break

              @case('notes')
                <textarea name="notes" id="notes" class="form-control" style="width:300px;height:90px">{{ old('notes', $initial) }}</textarea>
                @break

              @case('technical_visit')
                <textarea name="technical_visit" id="technical_visit" class="form-control" style="width:300px;height:70px">{{ old('technical_visit', $initial) }}</textarea>
                @break

              @default
                <input name="{{ $field }}" id="{{ $field }}" type="text" class="form-control" style="width:200px"
                       value="{{ old($field, $initial) }}">
            @endswitch
          </td>

          {{-- Radios por cada registro para elegir valor --}}
          @foreach($model as $i => $item)
            @php $val = data_get($item, $field); @endphp
            <td>
              <label class="form-check-inline" style="cursor:pointer">
                <input class="form-check-input pick-field"
                       type="radio"
                       name="pick_{{ $field }}"
                       value="{{ $item->id }}"                         {{-- id del registro (no se usa) --}}
                       data-target="#{{ $field }}"                      {{-- input/select destino --}}
                       data-val="{{ e((string)($val ?? '')) }}"         {{-- valor real --}}
                       {{ $i===0 ? 'checked' : '' }}>
                <span>{{ $controller->getModelText($field, $item) }} {{ $val }}</span>
              </label>
            </td>
          @endforeach
        </tr>
      @endforeach

      {{-- Acciones --}}
      <tr>
        <td><strong>Acciones</strong></td>
        <td></td>
        @foreach($model as $item)
          <td>
            <ul style="padding-left:18px;">
              @foreach($item->actions as $act)
                <li>
                  <label>
                    <input type="checkbox" name="action_all[]" value="{{ $act->id }}" checked>
                    {{ $act->note }}
                    <div>{{ optional($act->type)->name }}</div>
                    <div>{{ optional($act->creator)->name ?? 'Automático' }}</div>
                  </label>
                </li>
              @endforeach
            </ul>
          </td>
        @endforeach
      </tr>

      {{-- Archivos --}}
      <tr>
        <td><strong>Archivos</strong></td>
        <td></td>
        @foreach($model as $item)
          <td>
            <ul style="padding-left:18px;">
              @foreach($item->files as $f)
                <li>
                  <label>
                    <input type="checkbox" name="file_all[]" value="{{ $f->id }}" checked>
                    {{ $f->url }}
                  </label>
                </li>
              @endforeach
            </ul>
          </td>
        @endforeach
      </tr>
    </table>

    <div class="text-center">
      <button type="submit" class="btn btn-primary my-2">Consolidar</button>
    </div>
  </form>
</div>

{{-- JS: aplica el valor del radio seleccionado al input/select destino --}}
<script>
function applyToTarget(radio){
  const target = document.querySelector(radio.dataset.target);
  if (!target) return;
  const val = radio.dataset.val ?? '';
  if (target.tagName === 'SELECT') {
    const opt = Array.from(target.options).find(o => o.value == val);
    target.value = opt ? opt.value : '';
    target.dispatchEvent(new Event('change', {bubbles:true}));
  } else if (target.tagName === 'TEXTAREA') {
    target.value = val;
    target.dispatchEvent(new Event('input', {bubbles:true}));
  } else {
    target.value = val;
    target.dispatchEvent(new Event('input', {bubbles:true}));
  }
}

// Cambios en radios
document.addEventListener('change', function(e){
  if (e.target.classList.contains('pick-field')) {
    applyToTarget(e.target);
  }
});

// Inicializa cada grupo al cargar
document.addEventListener('DOMContentLoaded', function(){
  const groups = new Map();
  document.querySelectorAll('input.pick-field').forEach(r => {
    groups.set(r.name, (groups.get(r.name) || []).concat([r]));
  });
  groups.forEach(radios => {
    const checked = radios.find(r => r.checked) || radios[0];
    if (checked) applyToTarget(checked);
  });
});
</script>
@endsection
