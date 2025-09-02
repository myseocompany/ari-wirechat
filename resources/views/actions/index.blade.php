@extends('layout')

@section('content')

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://cdn.tailwindcss.com"></script>

<div class="flex min-h-screen">

    @include('actions.sidebar')

    @include('actions.main')

    @include('actions.modal_pending', ['action_options' => $action_options, 'statuses_options' => $statuses_options])

</div>



<style>
.input-date {
    max-width: 100%;
}
.custom-select, .form-control {
    max-width: 100%;
}
</style>

<script>
function submitWithRange(value) {
    document.getElementById('range_type').value = value;
    document.getElementById('filter_form').submit();
}

  function senForm() {
    document.getElementById('complete_action_form').submit();
  }

  function closeModal() {
    document.getElementById('pendingActionModal').classList.add('hidden');
  }

  function openModal() {
    document.getElementById('pendingActionModal').classList.remove('hidden');
  }

  $(document).ready(function() {
    $('[data-toggle="modal"]').on('click', function(event) {
      const button = $(this);
      const modal = $('#pendingActionModal');

      // Set data
      modal.find('#action_id').val(button.data('id'));
      modal.find('#pending_note').text(button.data('note'));
      modal.find('#type_id').val(button.data('type-id'));
      modal.find('#status_id').val(button.data('status-id'));
      modal.find('#customer_name').text(button.data('customer-name'));

      openModal();
    });
  });
</script>
@endsection
