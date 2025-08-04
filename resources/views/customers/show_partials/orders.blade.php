@if(isset($model->orders) && $model->orders->count())
  <div class="card mt-3">
    <h5 class="card-header">Cotizaciones del cliente</h5>
    <div class="card-body">
      @foreach($model->orders as $index => $order)
        <div class="mb-3 p-3 border rounded">
          <strong class="text-warning">Cotización #{{ $index + 1 }}</strong><br>
          <strong>Fecha:</strong> {{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y') }}<br>
          <strong>Total:</strong> ${{ number_format($order->getTotal(), 0, ',', '.') }}<br>
          <strong>Estado:</strong>
            <span class="badge" style="background-color: {{ optional($order->status)->color }}">{{ optional($order->status)->name }}</span><br>
          <a href="/orders/{{ $order->id }}/show" class="btn btn-outline-primary btn-sm mt-2">Ver cotización</a>
        </div>
      @endforeach
    </div>
  </div>
@endif
