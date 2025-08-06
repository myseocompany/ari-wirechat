<!-- Modal -->
<div id="pendingActionModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
  <div class="bg-white w-full max-w-lg rounded-lg shadow-lg">
    <div class="flex items-center justify-between px-4 py-2 border-b">
      <h5 class="text-lg font-semibold">Completar Tarea</h5>
      <button type="button" class="text-gray-500 hover:text-red-500 text-xl" onclick="closeModal()">&times;</button>
    </div>
    <form action="/customers/action/pending" method="POST" id="complete_action_form" class="px-4 py-3">
      {{ csrf_field() }}

      <div class="mb-4">
        <h3 id="customer_name" class="text-xl font-semibold mb-2"></h3>
        <div id="pending_note" class="text-gray-700 mb-2"></div>
        <textarea name="note" id="note" rows="5" class="w-full border rounded p-2 mb-4"></textarea>
        <input type="hidden" id="action_id" name="action_id" value="">
      </div>

      <div class="mb-4">
        <label for="type_id" class="block text-sm font-medium text-gray-700 mb-1">Seleccione una acción</label>
        <select name="type_id" id="type_id" class="w-full border rounded px-2 py-1">
          <option value="">Seleccione una acción</option>
          @foreach($action_options as $action_option)
          <option value="{{ $action_option->id }}">{{ $action_option->name }}</option>
          @endforeach
        </select>
      </div>

      <div class="mb-4">
        <label for="status_id" class="block text-sm font-medium text-gray-700 mb-1">Seleccione un estado</label>
        <select name="status_id" id="status_id" class="w-full border rounded px-2 py-1">
          <option value="">Seleccione un estado</option>
          @foreach($statuses_options as $item)
          <option value="{{ $item->id }}">{{ $item->name }}</option>
          @endforeach
        </select>
      </div>

      <div class="flex justify-end space-x-2 mt-4 border-t pt-3">
        <button type="button" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900" onclick="closeModal()">Cerrar</button>
        <button type="button" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700" onclick="senForm()">Guardar Cambios</button>
      </div>
    </form>
  </div>
</div>

<script>
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
  