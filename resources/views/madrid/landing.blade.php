@extends('layouts.app')

@section('content')
<div class="container">
  <h4 class="mb-4">Agenda programada — 16 y 17 de septiembre 2025</h4>

  {{-- Formulario de reagendar --}}
  <form action="{{ route('landing.booking.rebook') }}" method="POST" class="mb-4 row g-2">
    @csrf
    <div class="col-auto">
      <input type="text" name="phone" placeholder="Teléfono" class="form-control" required>
    </div>
    <div class="col-auto">
      <select name="date" class="form-select" required>
        <option value="">Selecciona día</option>
        <option value="2025-09-16">16/09/2025</option>
        <option value="2025-09-17">17/09/2025</option>
      </select>
    </div>
    <div class="col-auto">
      <select name="hour" class="form-select" required>
        @for ($h = 9; $h <= 17; $h++)
          @php
            $time = \Carbon\Carbon::create(2025, 1, 1, $h)->format('g A');
          @endphp
          <option value="{{ $h }}">{{ $time }}</option>
        @endfor
      </select>
    </div>
    <div class="col-auto">
      <button class="btn btn-primary">Reagendar</button>
    </div>
  </form>

  {{-- Tabla de agenda --}}
  <div class="table-responsive">
    <table class="table table-bordered text-center align-middle">
      <thead class="table-light">
        <tr>
          <th>Hora</th>
          <th>Martes 16</th>
          <th>Miércoles 17</th>
        </tr>
      </thead>
      <tbody>
        @for ($h = 9; $h <= 17; $h++)
          @php
            $slot16 = '2025-09-16-' . $h;
            $slot17 = '2025-09-17-' . $h;
            $timeLabel = \Carbon\Carbon::create(2025, 1, 1, $h)->format('g A');
          @endphp
          <tr>
            <td class="fw-bold">{{ $timeLabel }}</td>
            <td>{{ $resumen[$slot16] ?? 0 }}</td>
            <td>{{ $resumen[$slot17] ?? 0 }}</td>
          </tr>
        @endfor
      </tbody>
    </table>
  </div>
</div>
@endsection
