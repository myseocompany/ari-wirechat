@extends('layout')

@section('content')

<h1>Mensajes por cliente</h1>

<form action="/reports/views/customers_messages_count" method="GET" class="mb-3">
    <div class="row">
        <div class="col">
            <label for="from_date">Desde</label>
            <input class="input-date" type="date" id="from_date" name="from_date" value="{{ $fromDate?->format('Y-m-d') ?? $request->from_date }}">
        </div>
        <div class="col">
            <label for="to_date">Hasta</label>
            <input class="input-date" type="date" id="to_date" name="to_date" value="{{ $toDate?->format('Y-m-d') ?? $request->to_date }}">
        </div>
        <div class="col d-flex align-items-end">
            <input type="submit" class="btn btn-sm btn-primary my-2 my-sm-0" value="Filtrar">
        </div>
    </div>
</form>

<table class="table table-striped table-hover table-responsive">
    <thead class="thead-default">
        <tr>
            <th>Cliente</th>
            <th>Telefono</th>
            <th>Mensajes</th>
            <th>Ultimo mensaje</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($model as $item)
            <tr>
                <td>
                    <a href="{{ route('customers.show', $item->id) }}">{{ $item->name }}</a>
                </td>
                <td>{{ $item->phone }}</td>
                <td>{{ $item->messages_count }}</td>
                <td>{{ $item->last_message_at }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

@endsection
