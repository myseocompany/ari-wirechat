@extends('layout')

@section('content')
<div class="container">
    <h1>Customer Files – Year {{ $selectedYear }}</h1>

    {{-- Year filter --}}
    <form method="GET" action="{{ route('reports.missing_customer_files') }}" class="mb-4">
        <label for="year">Select year:</label>
        <select name="year" id="year" onchange="this.form.submit()" class="form-select w-auto d-inline-block ms-2">
            @foreach($availableYears as $year)
                <option value="{{ $year }}" {{ $year == $selectedYear ? 'selected' : '' }}>
                    {{ $year }}
                </option>
            @endforeach
        </select>
    </form>

    {{-- Accordion by month --}}
    <div class="accordion" id="monthsAccordion">
        @foreach($groupedByMonth as $month => $customers)
@php
    $customersCollection = collect($customers);
    $totalFiles = $customersCollection->sum('total_files');
    $totalMissing = $customersCollection->sum('missing_count');
    $monthId = Str::slug($month);
@endphp


            <div class="accordion-item">
                <h2 class="accordion-header" id="heading-{{ $monthId }}">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $monthId }}" aria-expanded="false" aria-controls="collapse-{{ $monthId }}">
                        {{ $month }} — {{ count($customers) }} customers · {{ $totalFiles }} files · {{ $totalMissing }} missing
                    </button>
                </h2>
                <div id="collapse-{{ $monthId }}" class="accordion-collapse collapse" aria-labelledby="heading-{{ $monthId }}" data-bs-parent="#monthsAccordion">
                    <div class="accordion-body">
                        @foreach($customers as $customer)
                            <div class="card mb-3">
                                <div class="card-header">
                                    <a href="https://arichat.co/customers/{{ $customer->id }}/show" target="_blank">
                                        <strong>{{ $customer->name }}</strong>
                                    </a>
                                    ({{ $customer->updated_at ?? 'N/A' }})
                                    <span class="ms-3 text-muted">{{ $customer->total_files }} files / {{ $customer->missing_count }} missing</span>
                                </div>
                                <div class="card-body">
                                    @if($customer->files->isEmpty())
                                        <p>No files for this customer.</p>
                                    @else
                                        <table class="table table-sm table-bordered">
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
                                                                <span class="badge bg-success">OK</span>
                                                            @else
                                                                <span class="badge bg-danger">MISSING</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
