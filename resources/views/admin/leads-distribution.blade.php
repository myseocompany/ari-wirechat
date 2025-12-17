@extends('layout')

@section('content')
<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h1 class="h4 mb-1">Distribución de leads</h1>
      <p class="text-muted mb-0">
        Ajusta el peso de cada asesor para definir qué porcentaje de leads recibirá. La suma debe ser 100%.
      </p>
    </div>
    <a href="{{ route('users') }}" class="btn btn-link">Ver usuarios</a>
  </div>

  @if (session('status'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('status') }}
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  @endif

  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form action="{{ route('admin.leads-distribution.update') }}" method="POST">
    @csrf
    <div class="table-responsive">
      <table class="table table-striped table-bordered">
        <thead>
          <tr>
            <th>Asesor</th>
            <th style="width:180px;">Peso (%)</th>
            <th>Último turno</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($users as $user)
            <tr>
              <td>
                <strong>{{ $user->name }}</strong>
              </td>
              <td>
                <input
                  type="number"
                  class="form-control form-control-sm"
                  name="weights[{{ $user->id }}]"
                  min="0"
                  max="100"
                  step="1"
                  data-weight-input
                  value="{{ old('weights.'.$user->id, $user->assignable ?? 0) }}"
                >
              </td>
              <td>
                @if ($user->last_assigned)
                  <span class="badge badge-primary">Recibió el último lead</span>
                @else
                  <span class="text-muted">—</span>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="3" class="text-center text-muted">No hay usuarios activos para asignación.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3">
      <div id="assignment-total-wrapper" class="font-weight-bold">
        Total actual: <span id="assignment-total">{{ $total }}</span>%
      </div>
      <button type="submit" class="btn btn-primary">Guardar cambios</button>
    </div>
  </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const inputs = document.querySelectorAll('[data-weight-input]');
  const totalElement = document.getElementById('assignment-total');
  const wrapper = document.getElementById('assignment-total-wrapper');

  const refreshTotal = () => {
    let total = 0;
    inputs.forEach((input) => {
      total += Number(input.value) || 0;
    });
    totalElement.textContent = total;
    wrapper.classList.toggle('text-danger', total !== 100);
    wrapper.classList.toggle('text-success', total === 100);
  };

  inputs.forEach((input) => {
    input.addEventListener('input', refreshTotal);
  });

  refreshTotal();
});
</script>
@endpush
