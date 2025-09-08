@extends('layout')

@section('content')
<div class="container">
    <h1>Customer Files – Year {{ $selectedYear }}</h1>

    {{-- Filtros --}}
    <form method="GET" action="{{ route('reports.missing_customer_files') }}" class="mb-4 form-inline">
        <label for="year" class="mr-2">Año:</label>
        <select name="year" id="year" onchange="this.form.submit()" class="form-control mr-4">
            @foreach($availableYears as $year)
                <option value="{{ $year }}" {{ $year == $selectedYear ? 'selected' : '' }}>
                    {{ $year }}
                </option>
            @endforeach
        </select>

        <label for="user_id" class="mr-2">Usuario asignado:</label>
        <select name="user_id" id="user_id" onchange="this.form.submit()" class="form-control">
            <option value="" {{ empty($selectedUserId) ? 'selected' : '' }}>Todos</option>
            <option value="unassigned" {{ $selectedUserId === 'unassigned' ? 'selected' : '' }}>Sin asignar</option>
            @foreach($users as $u)
                <option value="{{ $u->id }}" {{ (string)$selectedUserId === (string)$u->id ? 'selected' : '' }}>
                    {{ $u->name }}
                </option>
            @endforeach
        </select>
    </form>

    {{-- Acordeón por mes --}}
    <div class="accordion" id="monthsAccordion">
        @foreach($groupedByMonth as $monthData)
            @php
                $monthName = $monthData['name'];
                $customers = $monthData['customers'];
                $totalFiles = collect($customers)->sum('total_files');
                $totalMissing = collect($customers)->sum('missing_count');
                $monthId = Str::slug($monthName);
            @endphp

            <div class="card">
                <div class="card-header" id="heading-{{ $monthId }}">
                    <h5 class="mb-0">
                        <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapse-{{ $monthId }}" aria-expanded="false" aria-controls="collapse-{{ $monthId }}">
                            {{ $monthName }} — {{ $totalMissing }} archivos faltantes
                        </button>
                    </h5>
                </div>

                <div id="collapse-{{ $monthId }}" class="collapse" data-parent="#monthsAccordion">
                    <div class="card-body">
                        @php
  // Ordena por fecha desc si quieres
  $files = $model->customer_files->sortByDesc('created_at');
@endphp

<style>
  /* evita que nombres largos se corten/rompan el layout */
  .file-name { overflow-wrap: anywhere; word-break: break-word; }
</style>

<div class="row">
  @forelse($files as $file)
    @php
      $isMissing = isset($file->status) ? ($file->status === 'MISSING') : false;
    @endphp

    <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-3">
      <div class="card h-100 {{ $isMissing ? 'border-danger' : 'border-light' }} shadow-sm">
        <div class="card-body">
          <div class="file-name mb-2">
            @if(!$isMissing)
              <a href="/public/files/{{ $file->customer_id }}/{{ $file->url }}" target="_blank">
                {{ $file->url }}
              </a>
            @else
              <span class="text-muted">{{ $file->url }}</span>
            @endif
          </div>

          <small class="text-muted d-block">
            {{ optional($file->created_at)->format('Y-m-d H:i') }}
          </small>

          @if($isMissing)
            <span class="badge badge-danger mt-2">MISSING</span>
          @endif
        </div>

        <div class="card-footer bg-transparent border-0 pt-0">
          <div class="d-flex">
            @if(!$isMissing)
              <a class="btn btn-sm btn-outline-secondary mr-2"
                 href="/public/files/{{ $file->customer_id }}/{{ $file->url }}"
                 target="_blank">
                Abrir
              </a>
            @endif
            <a class="btn btn-sm btn-danger"
               href="/customer_files/{{ $file->id }}/delete">
              Eliminar
            </a>
          </div>
        </div>
      </div>
    </div>
  @empty
    <div class="col-12">
      <p class="text-muted mb-0">No hay archivos aún.</p>
    </div>
  @endforelse
</div>

                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
