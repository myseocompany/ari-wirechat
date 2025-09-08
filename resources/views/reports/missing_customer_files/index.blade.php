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
                $monthName   = $monthData['name'];
                $customers   = $monthData['customers'];
                $totalFiles  = collect($customers)->sum('total_files');
                $totalMissing= collect($customers)->sum('missing_count');
                $monthId     = Str::slug($monthName);
            @endphp

            <div class="card">
                <div class="card-header" id="heading-{{ $monthId }}">
                    <h5 class="mb-0">
                        <button class="btn btn-link collapsed" type="button" data-toggle="collapse"
                                data-target="#collapse-{{ $monthId }}" aria-expanded="false"
                                aria-controls="collapse-{{ $monthId }}">
                            {{ $monthName }} — {{ $totalMissing }} archivos faltantes
                        </button>
                    </h5>
                </div>

                <div id="collapse-{{ $monthId }}" class="collapse" data-parent="#monthsAccordion">
                    <div class="card-body">

                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Última actualización</th>
                                    <th>Total archivos</th>
                                    <th>Faltantes</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($customers as $customer)
                                    @php $rowId = 'customer-' . $monthId . '-' . $customer->id; @endphp
                                    <tr>
                                        <td>
                                            <a href="https://arichat.co/customers/{{ $customer->id }}/show" target="_blank">
                                                {{ $customer->name }}
                                            </a>
                                        </td>
                                        <td>{{ optional($customer->updated_at)->format('Y-m-d') }}</td>
                                        <td>{{ $customer->total_files }}</td>
                                        <td>{{ $customer->missing_count }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-secondary" type="button"
                                                    data-toggle="collapse" data-target="#{{ $rowId }}">
                                                Ver detalle
                                            </button>
                                        </td>
                                    </tr>

                                    {{-- Detalle del cliente (subida y lista de archivos) --}}
                                    <tr class="collapse" id="{{ $rowId }}">
                                        <td colspan="5">
                                            {{-- Subir archivos nuevos al cliente --}}
                                            <form method="POST" action="{{ route('customer_files.store') }}"
                                                  enctype="multipart/form-data" class="mb-3">
                                                @csrf
                                                <div class="form-row align-items-center">
                                                    <div class="col-auto">
                                                        <input type="file" name="files[]" multiple required class="form-control-file">
                                                        <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                                                    </div>
                                                    <div class="col-auto">
                                                        <button type="submit" class="btn btn-sm btn-primary">Subir archivos</button>
                                                    </div>
                                                </div>
                                            </form>

                                            @if($customer->files->isEmpty())
                                                <p class="text-muted mb-0">No files for this customer.</p>
                                            @else
                                                <style>
                                                    .file-path { font-family: monospace; }
                                                    .file-name { overflow-wrap:anywhere; word-break:break-word; }
                                                </style>

                                                <table class="table table-sm table-bordered mt-2">
                                                    <thead>
                                                        <tr>
                                                            <th>File Name</th>
                                                            <th>Path</th>
                                                            <th>Status</th>
                                                            <th>Acción</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($customer->files as $file)
                                                            @php $isMissing = ($file->status === 'MISSING'); @endphp
                                                            <tr>
                                                                <td class="file-name">{{ $file->url }}</td>
                                                                <td class="file-path">/files/{{ $customer->id }}/{{ $file->url }}</td>
                                                                <td>
                                                                    @if($isMissing)
                                                                        <span class="badge badge-danger">MISSING</span>
                                                                    @else
                                                                        <span class="badge badge-success">OK</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if($isMissing)
                                                                        {{-- Reponer el mismo archivo (misma URL en BD) --}}
                                                                        <form method="POST"
                                                                              action="{{ route('customer_files.reupload', $file->id) }}"
                                                                              enctype="multipart/form-data"
                                                                              class="form-inline">
                                                                            @csrf
                                                                            <input type="file" name="file" required
                                                                                   class="form-control-file mr-2" style="max-width:220px;">
                                                                            <button type="submit" class="btn btn-sm btn-warning">
                                                                                Reponer
                                                                            </button>
                                                                        </form>
                                                                    @else
                                                                        <a class="btn btn-sm btn-outline-secondary" target="_blank"
                                                                           href="/public/files/{{ $customer->id }}/{{ $file->url }}">
                                                                            Abrir
                                                                        </a>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
