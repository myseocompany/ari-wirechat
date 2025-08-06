<div class="space-y-4">
  @foreach ($model as $action)
    @php
      $customer = $action->customer;
      $status = $customer->status ?? null;
    @endphp

    <div class="bg-white shadow-sm border border-gray-200 rounded-xl p-4 relative flex justify-between items-start">

      <div class="w-full">
        {{-- Estado de acción --}}
        @if($action->isPending() && !empty($action->due_date))
          <span class="text-xs text-red-500 font-semibold">⏰ Programado para: {{ \Carbon\Carbon::parse($action->due_date)->format('d M Y H:i') }}</span>
        @endif

        {{-- Nota principal --}}
        <div class="text-lg font-bold text-gray-800 mt-1">
          {{ $action->note }}
        </div>

        {{-- Info acción --}}
        <div class="text-sm text-gray-600">
          {{ $action->getTypeName() }} 
          @if($action->creator)
            • Creado por {{ $action->creator->name }}
          @endif
        </div>
        @if(isset($customer))
        {{-- Info cliente --}}
        <div class="mt-2 text-sm">
          <a href="/customers/{{ $action->customer_id }}/show" class="text-blue-600 font-semibold">
            {{ $customer->name }}
          </a>
          @if($status)
            <span class="ml-2 inline-block px-2 py-0.5 text-xs text-white rounded" style="background-color: {{ $status->color }}">
              {{ $status->name }}
            </span>
          @endif

          <div class="text-gray-500 text-xs mt-1">
            📞 <a href="tel:{{ $customer->phone }}">{{ $customer->phone }}</a> • ✉️ {{ $customer->email }}
            @if($customer->project) • 🏡 {{ $customer->project->name }} @endif
            @if($customer->source) • 🎯 {{ $customer->source->name }} @endif
          </div>
        </div>
        @endif

      </div>

      {{-- Checkbox para completar --}}
      @if($action->isPending())
        <div class="ml-4 flex-shrink-0">
          <input
            type="checkbox"
            data-toggle="modal"
            data-id="{{ $action->id }}"
            data-note="{{ $action->note }}"
            data-type-id="{{ $action->type_id }}"
            data-status-id="{{ optional($customer)->status_id }}"
            data-customer-name="{{ optional($customer)->name }}"
            class="w-6 h-6 rounded-full border-2 border-blue-500 text-blue-600 focus:ring-2 focus:ring-blue-400 checked:bg-blue-600 checked:border-transparent"
            onclick="this.checked=false"
          >
        </div>
      @endif
    </div>
  @endforeach
</div>
