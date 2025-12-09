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

        var badgesSelector = $form.data('tags-badges');
        var feedbackSelector = $form.data('tags-feedback');
        var $badgesContainer = badgesSelector ? $scope.find(badgesSelector) : $form.prev('.tags-badges');
        var hasBadges = $badgesContainer && $badgesContainer.length > 0;
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
                'border-color': checked ? color : '#e2e8f0'
              });
            }
          });
        }

        function renderBadgesFromSelection() {
          updateLabelStyles();
          if (!hasBadges) return;

          var selected = [];
          $form.find('.tag-checkbox:checked').each(function() {
            selected.push({
              name: $(this).data('name'),
              color: $(this).data('color') || '#e2e8f0'
            });
          });

          if (!selected.length) {
            $badgesContainer.html('<span class="text-muted">Sin etiquetas</span>');
            return;
          }

          var html = selected.map(function(tag) {
            return '<span class="px-2 py-1 rounded-full text-xs font-semibold mr-2 mb-1 d-inline-block" style="background-color: ' + tag.color + ';">' + tag.name + '</span>';
          }).join('');
          $badgesContainer.html(html);
        }

        function sendTags() {
          var payload = $form.serializeArray();
          if (!$form.find('.tag-checkbox:checked').length) {
            payload.push({ name: 'tags', value: '' });
          }

          $feedback.text('Guardando etiquetas...');
          $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: $.param(payload),
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(resp) {
              $feedback.text(resp.message || 'Etiquetas actualizadas.');
              renderBadgesFromSelection();
            },
            error: function() {
              $feedback.text('No se pudieron guardar las etiquetas.');
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
