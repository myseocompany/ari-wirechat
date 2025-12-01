@extends('layout')

@section('content')
<h2 class="mb-4">Órdenes</h2>

@include('orders.dashboard')
@include('orders.filter')

<div class="table-responsive">
  <table class="table table-hover align-middle">
  <thead class="table-light">
    <tr>
      
      <th>Estado</th>
      <th>Cliente</th>
      <th>Asesor</th> 
      <th>Productos</th>
      <th>Empresa</th>
      <th>Pais</th>
      <th>Total</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
    @foreach($model as $item)
    @php
      $customer = $item->customer;
      $user = $item->user;
    @endphp
    <tr>
      
      <td>
        @if($customer && $customer->status)
          <span class="badge" style="background-color: {{ $customer->status->color }}">
            {{ $customer->status->name }}
          </span>
        @else
          <span class="badge bg-secondary">Sin estado</span>
        @endif
      </td>

      <td>
  <div class="d-flex align-items-center">
    @php
      $initials = strtoupper(Str::substr($customer?->name ?? '??', 0, 2));
    @endphp
    <div class="rounded-circle bg-success text-white me-2 d-flex justify-content-center align-items-center" style="width: 36px; height: 36px;">
      {{ $initials }}
    </div>
    <div>
      <a href="{{ $customer ? route('customers.show', $customer->id) : '#' }}" class="text-dark text-decoration-none">
        <strong>{{ $customer?->name ?? 'Desconocido' }}</strong>
      </a><br>
      <small>{{ $customer?->getPhone() ?? '' }}</small>
    </div>
  </div>
</td>


      

<td>
  <div class="d-flex align-items-center">
    @php
      $userName = $user?->name ?? '??';
      $initials = collect(explode(' ', $userName))->map(fn($s) => strtoupper(substr($s, 0, 1)))->implode('');
    @endphp
    <div class="rounded-circle bg-primary text-white me-2 d-flex justify-content-center align-items-center" style="width: 36px; height: 36px;">
      {{ $initials }}
    </div>
    <div>
      <strong>{{ $user?->name ?? 'Sin asignar' }}</strong>
    </div>
  </div>
</td>

    <td>
  @if($item->products && $item->products->count())
    <ul class="mb-0 ps-3">
      @foreach($item->products as $product)
        <li>{{ $product->name }}</li>
      @endforeach
    </ul>
  @else
    <em>-</em>
  @endif
</td>


      

    <td>
    
      {{ $customer?->business ?? 'N/A' }}
 
  </td>

      <td>
        
         {{ $customer?->country ?? 'N/A' }}
      
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



<div class="mt-4">
  {{ $model->appends(request()->input())->links() }}
</div>

@endsection
