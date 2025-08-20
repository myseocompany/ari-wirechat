@extends('layout')

@section('content')
<div class="container">
    <h1>Customer Files – Year {{ $selectedYear }}</h1>

    {{-- Year selector --}}
    <form method="GET" action="{{ route('reports.missing_customer_files') }}" class="mb-4">
        <label for="year">Select year:</label>
        <select name="year" id="year" onchange="this.form.submit()" class="form-control w-auto d-inline-block ml-2">
            @foreach($availableYears as $year)
                <option value="{{ $year }}" {{ $year == $selectedYear ? 'selected' : '' }}>
                    {{ $year }}
                </option>
            @endforeach
        </select>
    </form>

    {{-- Accordion by month --}}
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
                                        <td>{{ $customer->updated_at->format('Y-m-d') }}</td>
                                        <td>{{ $customer->total_files }}</td>
                                        <td>{{ $customer->missing_count }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-secondary" type="button" data-toggle="collapse" data-target="#{{ $rowId }}">
                                                Ver detalle
                                            </button>
                                        </td>
                                    </tr>
                                    <tr class="collapse" id="{{ $rowId }}">
                                        <td colspan="5">
                                            @if($customer->files->isEmpty())
                                                <p>No files for this customer.</p>
                                            @else
                                                <table class="table table-sm table-bordered mt-2">
                                                    <thead>
                                                        <tr>
                                                            <th>File Name</th>
                                                            <th>Path</th>
                                                            <th>Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($customer->files as $file)
                                                            <tr>
                                                                <td>{{ $file->url }}</td>
                                                                <td>/files/{{ $customer->id }}/{{ $file->url }}</td>
                                                                <td>
                                                                    @if($file->status === 'OK')
                                                                        <span class="badge badge-success">OK</span>
                                                                    @else
                                                                        <span class="badge badge-danger">MISSING</span>
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
