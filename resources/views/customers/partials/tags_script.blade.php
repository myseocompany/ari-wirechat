@push('scripts')
<script>
  (function() {
    if (window.initCustomerTags) return;

    window.initCustomerTags = function(scope) {
      var $scope = scope ? $(scope) : $(document);

      $scope.find('form.customer-tags-form').each(function() {
        var $form = $(this);
        if ($form.data('tags-bound')) return;
        $form.data('tags-bound', true);

        var feedbackSelector = $form.data('tags-feedback');
        var $feedback = feedbackSelector ? $scope.find(feedbackSelector) : $form.next('.tags-feedback');

        function updateLabelStyles() {
          $form.find('.tag-checkbox').each(function() {
            var $checkbox = $(this);
            var $label = $checkbox.closest('label');
            var color = $checkbox.data('color') || '#e2e8f0';
            var checked = $checkbox.is(':checked');
            $label.css({
              'border-color': '#e2e8f0',
              'background-color': 'transparent'
            });
            var $swatch = $label.find('.tag-swatch');
            if ($swatch.length) {
              $swatch.css({
                'background-color': checked ? color : 'transparent',
                'border-color': checked ? color : '#e2e8f0',
                'color': checked ? '#fff' : '#000'
              });
            }
          });
        }

        function renderBadgesFromSelection() {
          updateLabelStyles();
        }

        function sendTags() {
          var payload = $form.serializeArray();
          if (!$form.find('.tag-checkbox:checked').length) {
            payload.push({ name: 'tags', value: '' });
          }

          if ($feedback.length) {
            $feedback.text('');
          }
          $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: $.param(payload),
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(resp) {
              if ($feedback.length) {
                $feedback.text('');
              }
              renderBadgesFromSelection();
            },
            error: function() {
              if ($feedback.length) {
                $feedback.text('No se pudieron guardar las etiquetas.');
              }
            }
          });
        }

        $form.on('change.tag-sync', '.tag-checkbox', sendTags);
        renderBadgesFromSelection();
      });
    };

    $(document).ready(function() {
      window.initCustomerTags();
    });
  })();
</script>
@endpush
