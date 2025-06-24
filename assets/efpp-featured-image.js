(function ($) {
  $(document).ready(function () {
    // Otwarcie Media Library po kliknięciu w obrazek lub tło
    $(document).on('click', '.efpp-preview, .efpp-drop-zone:not(.has-image)', function () {
      const wrapper = $(this).closest('.efpp-featured-image-wrapper');
      const input = wrapper.find('input[type="hidden"]');

      const frame = wp.media({
        title: 'Wybierz obrazek',
        multiple: false,
        library: { type: 'image' },
        button: { text: 'Ustaw obrazek' }
      });

      frame.on('select', function () {
        const attachment = frame.state().get('selection').first().toJSON();
        wrapper.find('.efpp-preview').attr('src', attachment.url).show();
        if (!input.length) {
          console.warn('EFPP: input[type=hidden] not found');
          return;
        }
          if (!input.attr('name')) {
          const fallbackName = wrapper.data('field-name');
          input.attr('name', 'form_fields[' + fallbackName + ']');
        }
        input.val(attachment.url).trigger('change');
        console.log('Set image URL:', attachment.url);

        wrapper.find('.efpp-drop-zone').addClass('has-image');
        wrapper.find('.efpp-remove-image').show();
      });

      frame.open();
    });

    // Usuwanie obrazka
    $(document).on('click', '.efpp-remove-image', function (e) {
      e.preventDefault();
      const wrapper = $(this).closest('.efpp-featured-image-wrapper');
      wrapper.find('.efpp-preview').attr('src', '').hide();
      wrapper.find('input[type="hidden"]').val('');
      wrapper.find('.efpp-drop-zone').removeClass('has-image');
      $(this).hide();
    });

    // Obsługa dragover
    $(document).on('dragover', '.efpp-drop-zone', function (e) {
      e.preventDefault();
      e.stopPropagation();
      $(this).addClass('dragover');
    });

    // Obsługa dragleave
    $(document).on('dragleave', '.efpp-drop-zone', function (e) {
      e.preventDefault();
      e.stopPropagation();
      $(this).removeClass('dragover');
    });

    // Obsługa drop
    $(document).on('drop', '.efpp-drop-zone', function (e) {
      e.preventDefault();
      e.stopPropagation();
      $(this).removeClass('dragover');

      const file = e.originalEvent.dataTransfer.files[0];
      if (!file || !file.type.match('image.*')) return;

      const wrapper = $(this).closest('.efpp-featured-image-wrapper');
      const input = wrapper.find('input[type="hidden"]');

      const formData = new FormData();
      formData.append('file', file);
      formData.append('action', 'efpp_upload_image');
      formData.append('_wpnonce', EFPPImageField.nonce);

      $.ajax({
        url: EFPPImageField.ajax_url,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function (res) {
          if (res.success && res.data.url) {
            wrapper.find('.efpp-preview').attr('src', res.data.url).show();
            input.val(res.data.url);
            wrapper.find('.efpp-drop-zone').addClass('has-image');
            wrapper.find('.efpp-remove-image').show();
          }
        }
      });
    });
  });
})(jQuery);
