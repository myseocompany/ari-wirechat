@extends('layout')

@section('content')
<h2 class="mb-4">Órdenes</h2>

@include('orders.dashboard')
@include('orders.filter')

<div class="table-responsive">
  <table class="table table-hover align-middle">
  <thead class="table-light">
    <tr>
      <th>Factura</th>
      <th>Estado</th>
      <th>Cliente</th>
      <th>Producto</th>
      <th>Asesor</th> {{-- ← NUEVA COLUMNA --}}
      <th>Método</th>
      <th>Entrega</th>
      <th>Total</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
    @foreach($model as $item)
    <tr>
      <td><span class="badge bg-primary">{{ $item->id }}</span></td>

      <td>
        @if($item->status)
          <span class="badge" style="background-color: {{ $item->status->color }}">
            {{ $item->status->name }}
          </span>
        @else
          <span class="badge bg-secondary">Sin estado</span>
        @endif
      </td>

      <td class="d-flex align-items-center">
        @php
          $initials = strtoupper(Str::substr($item->customer->name ?? '??', 0, 2));
        @endphp
        <div class="rounded-circle bg-success text-white me-2 d-flex justify-content-center align-items-center" style="width: 36px; height: 36px;">
          {{ $initials }}
        </div>
        <div>
          <strong>{{ $item->customer->name ?? 'Desconocido' }}</strong><br>
          <small>{{ $item->customer->getPhone() ?? '' }}</small>
        </div>
      </td>

      <td>{{ $item->firstProductName() }}</td>

    <td class="d-flex align-items-center">
      @php
        $userName = $item->user->name ?? '??';
        $initials = collect(explode(' ', $userName))->map(fn($s) => strtoupper(substr($s, 0, 1)))->implode('');
      @endphp

      <div class="rounded-circle bg-primary text-white me-2 d-flex justify-content-center align-items-center" style="width: 36px; height: 36px;">
        {{ $initials }}
      </div>
      <div>
        <strong>{{ $item->user->name ?? 'Sin asignar' }}</strong><br>
        
      </div>
    </td>


      <td>{{ $item->payment->name ?? '-' }}</td>

      <td>
        <span class="badge bg-light text-danger">
          {{ optional($item->delivery_date)->format('h:i A') }}<br>
          {{ optional($item->delivery_date)->format('d M, Y') }}
        </span>
      </td>

      <td class="text-end">
        <strong>$ {{ number_format($item->getTotal(), 0, ',', '.') }}</strong>
      </td>

      <td class="text-end">
        <a href="/orders/{{ $item->id }}/show" class="text-primary me-2" title="Ver"><i class="fa fa-eye"></i></a>
        <a href="/orders/{{ $item->id }}/edit" class="text-warning" title="Editar"><i class="fa fa-edit"></i></a>
      </td>
    </tr>
    @endforeach
  </tbody>
</table>

</div>

{{-- Totales por método de pago --}}
@if(isset($payments))
  <div class="mt-4">
    <h5>Totales por forma de pago</h5>
    <ul class="list-group">
      @foreach($payments as $item)
        <li class="list-group-item d-flex justify-content-between">
          <strong>{{ $item->name }}</strong>
          <span>$ {{ number_format($total_payments[$item->id] ?? 0, 0, ',', '.') }}</span>
        </li>
      @endforeach
    </ul>
  </div>
@endif

<div class="mt-4">
  {{ $model->appends(request()->input())->links() }}
</div>

@endsection
