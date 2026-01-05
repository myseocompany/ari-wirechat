@php
  $orders = $model->orders ?? collect();
  $quickOrderCountries = $productCountries ?? [];
  $quickOrderProducts = $productsByCountry ?? [];
  $quickOrderCountryValue = old('country', $model->country ?? '');
@endphp

<div class="bg-white space-y-6">
  <div class="space-y-3">
    <div class="flex items-center justify-between">
      <h3 class="text-base font-semibold text-slate-900">Orden rápida</h3>
      <span class="text-xs text-slate-500">Crear cotización en segundos</span>
    </div>
    @if($errors->quickOrder->any())
      <div class="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700">
        <ul class="space-y-1">
          @foreach($errors->quickOrder->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif
    <form method="POST" action="{{ route('orders.quick') }}" class="space-y-3">
      @csrf
      <input type="hidden" name="customer_id" value="{{ $model->id }}">
      <label class="flex flex-col gap-2 text-sm font-medium text-slate-700">
        País
        <select id="quick_order_country" name="country" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm text-slate-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
          <option value="">Selecciona un país</option>
          @foreach($quickOrderCountries as $country)
            <option value="{{ $country }}" @selected($quickOrderCountryValue === $country)>{{ $country }}</option>
          @endforeach
        </select>
      </label>
      <label class="flex flex-col gap-2 text-sm font-medium text-slate-700">
        Producto
        <select id="quick_order_product" name="product_id" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm text-slate-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
          <option value="">Selecciona un producto</option>
        </select>
      </label>
      <button type="submit" class="inline-flex items-center justify-center rounded-md bg-blue-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-blue-700">
        Crear orden rápida
      </button>
    </form>
  </div>

  <div class="space-y-3">
    <div>
      <h3 class="text-base font-semibold text-slate-900">Cotizaciones del cliente</h3>
    </div>
    @if($orders->count())
      <div class="space-y-3 text-sm text-slate-700">
        @foreach($orders as $index => $order)
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
    @else
      <div class="rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-500">
        Aún no hay cotizaciones registradas para este cliente.
      </div>
    @endif
  </div>
</div>

@push('scripts')
  <script>
    (function () {
      var productsByCountry = @json($quickOrderProducts);
      var countrySelect = document.getElementById('quick_order_country');
      var productSelect = document.getElementById('quick_order_product');
      if (!countrySelect || !productSelect) {
        return;
      }
      var initialProductId = '{{ old('product_id') }}';

      function resetProducts() {
        productSelect.innerHTML = '<option value="">Selecciona un producto</option>';
      }

      function renderProducts(country) {
        resetProducts();
        if (!country || !productsByCountry[country]) {
          return;
        }
        productsByCountry[country].forEach(function (product) {
          var option = document.createElement('option');
          var priceValue = product.price ? Number(product.price) : null;
          var price = priceValue ? ' · $' + priceValue.toLocaleString('es-CO') : '';
          var coin = product.coin ? ' ' + product.coin : '';
          option.value = product.id;
          option.textContent = product.name + price + coin;
          if (initialProductId && String(product.id) === String(initialProductId)) {
            option.selected = true;
          }
          productSelect.appendChild(option);
        });
      }

      renderProducts(countrySelect.value);
      countrySelect.addEventListener('change', function () {
        initialProductId = '';
        renderProducts(countrySelect.value);
      });
    })();
  </script>
@endpush
