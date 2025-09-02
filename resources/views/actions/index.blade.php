@extends('layout')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://cdn.tailwindcss.com"></script>

<style>
  .input-date { max-width:100%; }
  .custom-select, .form-control { max-width:100%; }
</style>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0">Acciones</h4>
  <div class="btn-group">
    @php $q = request()->except('view'); @endphp
    <a href="{{ route('actions.index', array_merge($q, ['view'=>'list'])) }}"
       class="btn btn-sm {{ ($view ?? 'list')==='list' ? 'btn-primary' : 'btn-outline-primary' }}">
       Lista
    </a>
    <a href="{{ route('actions.index', array_merge($q, ['view'=>'calendar'])) }}"
       class="btn btn-sm {{ ($view ?? 'list')==='calendar' ? 'btn-primary' : 'btn-outline-primary' }}">
       Calendario
    </a>
  </div>
</div>

<div class="flex min-h-screen">
  @include('actions.sidebar')

  @if(($view ?? 'list') === 'calendar')
    {{-- Vista calendario --}}
    @include('actions.calendar_panel', ['types' => $types, 'users' => $users])
  @else
    {{-- Vista lista --}}
    @include('actions.main')
    @include('actions.modal_pending', [
      'action_options' => $action_options,
      'statuses_options' => $statuses_options
    ])
  @endif
</div>

<script>
function submitWithRange(value) {
  document.getElementById('range_type').value = value;
  document.getElementById('filter_form').submit();
}
function senForm(){ document.getElementById('complete_action_form').submit(); }
function closeModal(){ document.getElementById('pendingActionModal').classList.add('hidden'); }
function openModal(){ document.getElementById('pendingActionModal').classList.remove('hidden'); }

$(function(){
  $('[data-toggle="modal"]').on('click', function(){
    const b = $(this), m = $('#pendingActionModal');
    m.find('#action_id').val(b.data('id'));
    m.find('#pending_note').text(b.data('note'));
    m.find('#type_id').val(b.data('type-id'));
    m.find('#status_id').val(b.data('status-id'));
    m.find('#customer_name').text(b.data('customer-name'));
    openModal();
  });
});
</script>
@endsection
