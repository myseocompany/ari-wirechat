@if(isset($model->orders) && $model->orders->count())
  <div class="bg-white">
    <div class="">
      <h3 class="text-base font-semibold text-slate-900">Cotizaciones del cliente</h3>
    </div>
    <div class="space-y-3 text-sm text-slate-700">
      @foreach($model->orders as $index => $order)
        <div class="bg-slate-50 p-3">
          <strong class="text-amber-600">Cotización #{{ $index + 1 }}</strong><br>
          <strong>Fecha:</strong> {{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y') }}<br>

          {{-- Productos cotizados --}}
          @if($order->productList->count() > 0)
            <strong>Productos:</strong>
            <ul class="mt-1 space-y-1">
              @foreach($order->productList as $product)
                <li>
                  {{ $product->quantity }} × {{ $product->product->name ?? 'Producto eliminado' }}
                  @if($product->price)
                    - ${{ number_format($product->price, 0, ',', '.') }}
                  @endif
                </li>
              @endforeach
            </ul>
          @endif

          <strong>Total:</strong> ${{ number_format($order->getTotal(), 0, ',', '.') }}<br>
          <strong>Estado:</strong>
            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold text-white" style="background-color: {{ optional($order->status)->color }}">
              {{ optional($order->status)->name ?? 'Sin estado' }}
            </span><br>
          <a href="/orders/{{ $order->id }}/show" class="mt-2 inline-flex items-center rounded-md border border-blue-600 px-3 py-1.5 text-xs font-semibold text-blue-600 transition hover:bg-blue-50">Ver cotización</a>
        </div>
      @endforeach
    </div>
  </div>
@endif
