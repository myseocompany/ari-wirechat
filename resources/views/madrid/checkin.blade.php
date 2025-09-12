@extends('layouts.landing')

@section('content')
<div class="flex justify-center items-center min-h-screen p-4">
  <div class="w-full max-w-xl bg-white shadow-lg rounded-lg p-6">

    <h2 class="text-2xl font-bold text-center mb-6">Check-in: Tour de la Empanada 2025</h2>

    {{-- Notificación tipo toast --}}
    <div id="toast" class="fixed top-4 right-4 bg-green-100 text-green-800 px-4 py-2 rounded shadow hidden"></div>

    {{-- Primera pantalla: teléfono --}}
    @if(!isset($customer))
      <form action="{{ route('landing.checkin.submit') }}" method="POST" class="flex flex-col gap-4">
        @csrf
        <input type="text" name="phone" placeholder="Ingrese su teléfono con indicativo (34)" required
          class="border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Continuar</button>
        @if($errors->any())
          <div class="text-red-600 mt-2">@foreach($errors->all() as $e) {{ $e }} <br> @endforeach</div>
        @endif
      </form>

    @else
      {{-- Segunda pantalla --}}
      <p class="mb-2 text-gray-700 text-center">Hola, <strong>{{ $nombre }}</strong>!</p>

      @if(isset($cita))
        <p class="mb-4 text-gray-700 text-center">
          Su cita actual: <strong>{{ \Carbon\Carbon::parse($cita->due_date)->format('d/m/Y g A') }}</strong>
        </p>

        <div class="flex justify-center mb-4">
          <button onclick="cancelBooking('{{ $customer->phone }}')" 
            class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
            Cancelar cita
          </button>
        </div>
      @endif

      <p class="text-center mb-4 text-gray-600">Abajo se muestra la cantidad de personas por día. 
        Colores indican la ocupación: <span class="bg-green-200 px-1 rounded">Normal</span>, 
        <span class="bg-yellow-200 px-1 rounded">Medio</span>, 
        <span class="bg-red-200 px-1 rounded">Muy ocupado</span>.
        Puede reagendar dando click en una nueva hora/día.
      </p>

      {{-- Tabla resumen --}}
      <div class="overflow-x-auto">
        <table class="table-auto w-full border border-gray-200 text-center">
          <thead>
            <tr class="bg-gray-100">
              <th class="px-4 py-2 border">Hora</th>
              <th class="px-4 py-2 border">Martes 16</th>
              <th class="px-4 py-2 border">Miércoles 17</th>
            </tr>
          </thead>

<tbody>
@for ($h=9;$h<=17;$h++)
    @php
        $slot16 = '2025-09-16-'.$h;
        $slot17 = '2025-09-17-'.$h;
        $timeLabel = \Carbon\Carbon::create(2025,1,1,$h)->format('g A');

    $currentSlot16 = isset($cita) && \Carbon\Carbon::parse($cita->due_date)->format('Y-m-d') == '2025-09-16'
                     ? '2025-09-16-' . intval(\Carbon\Carbon::parse($cita->due_date)->hour)
                     : null;

    $currentSlot17 = isset($cita) && \Carbon\Carbon::parse($cita->due_date)->format('Y-m-d') == '2025-09-17'
                     ? '2025-09-17-' . intval(\Carbon\Carbon::parse($cita->due_date)->hour)
                     : null;


        $getColor = function($count){
            if($count <= 10) return 'bg-green-200';
            if($count <= 20) return 'bg-yellow-200';
            return 'bg-red-200';
        };
    @endphp
    <tr>
        <td class="px-4 py-2 border font-semibold">{{ $timeLabel }}</td>

        {{-- Slot 16 --}}
<td class="px-4 py-2 border text-blue-600 relative
    @if($currentSlot16 == $slot16) bg-green-300 font-bold cursor-default
    @else cursor-pointer hover:bg-gray-100 {{ $getColor($resumen[$slot16] ?? 0) }} @endif"
    @if($currentSlot16 != $slot16)
        onclick="rebook('{{ $customer->phone }}','2025-09-16','{{ $h }}')"
    @endif>
    <div class="flex justify-center items-center relative">
        {{ $resumen[$slot16] ?? 0 }}
        @if($currentSlot16 == $slot16)
            <span class="absolute -top-2 right-0 text-xs bg-blue-200 px-1 rounded">Aquí está usted</span>
        @endif
    </div>
</td>

{{-- Slot 17 --}}
<td class="px-4 py-2 border text-blue-600 relative
    @if($currentSlot17 == $slot17) bg-green-300 font-bold cursor-default
    @else cursor-pointer hover:bg-gray-100 {{ $getColor($resumen[$slot17] ?? 0) }} @endif"
    @if($currentSlot17 != $slot17)
        onclick="rebook('{{ $customer->phone }}','2025-09-17','{{ $h }}')"
    @endif>
    <div class="flex justify-center items-center relative">
        {{ $resumen[$slot17] ?? 0 }}
        @if($currentSlot17 == $slot17)
            <span class="absolute -top-2 right-0 text-xs bg-blue-200 px-1 rounded">Aquí está usted</span>
        @endif
    </div>
</td>


    </tr>
@endfor

</tbody>

        </table>
      </div>

    @endif
  </div>
</div>

<script>
function rebook(phone, date, hour) {
    if(!confirm(`Desea reagendar su cita al ${date} a las ${hour}:00?`)) return;

    fetch("{{ route('landing.rebook') }}", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ phone, date, hour })
    })
    .then(res => res.json())
    .then(data => {
        if(data.success){
            const toast = document.getElementById('toast');
            toast.textContent = data.success;
            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.add('hidden'), 4000);
            location.reload();
        }
    });
}

function cancelBooking(phone) {
    if(!confirm("¿Desea cancelar su cita actual?")) return;

    fetch("{{ route('landing.cancel') }}", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ phone })
    })
    .then(res => res.json())
    .then(data => {
        if(data.success){
            const toast = document.getElementById('toast');
            toast.textContent = data.success;
            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.add('hidden'), 4000);
            location.reload();
        }
    });
}
</script>
@endsection
