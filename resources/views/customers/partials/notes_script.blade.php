@push('scripts')
<script>
(function() {
  window.initNotesEditors = function(scope) {
    var $scope = scope ? $(scope) : $(document);
    $scope.find('.notes-editor').each(function() {
      var $editor = $(this);
      if ($editor.data('notes-bound')) return;
      $editor.data('notes-bound', true);

      var $display = $editor.find('.notes-display');
      var $feedback = $editor.find('.notes-feedback');
      var saveUrl = $editor.data('save-url') || $display.data('save-url');
      var $btn = $editor.find('.notes-edit-btn');
      var modalSelector = $btn.data('modal');
      var $modal = modalSelector ? $(modalSelector) : null;
      var $textarea = $modal ? $modal.find('.notes-textarea') : null;
      var $save = $modal ? $modal.find('.notes-save-btn') : null;
      var lastValue = $display.text();

      function persist(newText) {
        var payload = newText || '';
        if ($feedback.length) $feedback.text('Guardando...');
        $.ajax({
          url: saveUrl,
          method: 'POST',
          data: {
            notes: payload,
            _token: $('meta[name="csrf-token"]').attr('content')
          },
          success: function(resp) {
            var updated = resp && typeof resp.notes !== 'undefined' ? resp.notes : payload;
            lastValue = updated;
            $display.text(updated);
            if ($feedback.length) $feedback.text('Notas guardadas');
            if ($modal) $modal.modal('hide');
          },
          error: function() {
            if ($feedback.length) $feedback.text('No se pudo guardar las notas');
          }
        });
      }

      // Inline
      $display.on('focus', function() {
        $display.addClass('notes-display--active');
        lastValue = $display.text();
      });
      $display.on('blur', function() {
        $display.removeClass('notes-display--active');
        var current = $display.text();
        if (current === lastValue) return;
        persist(current);
      });

      // Modal
      if ($btn.length && $modal && $textarea && $save) {
        if ($modal.parent()[0] !== document.body) {
          $modal.appendTo('body');
        }
        $btn.on('click', function() {
          $('.modal-backdrop').remove();
          $('body').removeClass('modal-open');
          $textarea.val($display.text());
          $modal.modal('show');
          $modal.off('shown.bs.modal.notes').on('shown.bs.modal.notes', function() {
            $textarea.trigger('focus');
          });
        });
        $save.on('click', function() {
          var current = ($textarea.val() || '');
          if (current === lastValue) {
            $modal.modal('hide');
            return;
          }
          persist(current);
        });
      }
    });
  };

  $(function() {
    if (window.initNotesEditors) {
      window.initNotesEditors();
    }
  });
})();
</script>
@endpush
