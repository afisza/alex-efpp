(function ($) {
  $(document).ready(function () {
    // Dodajemy sortable
    $('.efpp-image-list').sortable({
      items: '.efpp-image-item',
      update: function (event, ui) {
        updateInputs(ui.item.closest('.efpp-featured-image-wrapper'));
      }
    });

    // Obsługa kliknięcia w drop zone
    $(document).on('click', '.efpp-drop-zone', function () {
      const wrapper = $(this).closest('.efpp-featured-image-wrapper');
      const fieldName = wrapper.data('field-name');

      const frame = wp.media({
        title: 'Wybierz obrazki',
        multiple: true,
        library: { type: 'image' },
        button: { text: 'Dodaj obrazki' }
      });

      frame.on('select', function () {
        const selection = frame.state().get('selection');
        selection.each(function (attachment) {
          const img = attachment.toJSON();
          appendImage(wrapper, img.url);
        });
        updateInputs(wrapper);
      });

      frame.open();
    });

    // Dodawanie nowego obrazka do listy
    function appendImage(wrapper, url) {
      const imageItem = $(`
        <li class="efpp-image-item">
          <img src="${url}" />
          <button type="button" class="efpp-remove-image">×</button>
        </li>
      `);
      wrapper.find('.efpp-image-list').append(imageItem);
    }

    // Usuwanie obrazka
    $(document).on('click', '.efpp-remove-image', function (e) {
      e.preventDefault();
      const wrapper = $(this).closest('.efpp-featured-image-wrapper');
      $(this).closest('.efpp-image-item').remove();
      updateInputs(wrapper);
    });

    // Aktualizacja inputów hidden
    function updateInputs(wrapper) {
      const images = wrapper.find('.efpp-image-item img').map(function () {
        return $(this).attr('src');
      }).get();

      const featured = images[0] || '';
      const gallery = images.slice(1);

      wrapper.find('input[name^="form_fields["][name*="featured_image"]').val(featured);
      wrapper.find('input[name^="form_fields["][name*="gallery"]').val(gallery.join(','));
    }

    // Obsługa przeciągania pliku
    $(document).on('dragover', '.efpp-drop-zone', function (e) {
      e.preventDefault();
      $(this).addClass('dragover');
    });

    $(document).on('dragleave', '.efpp-drop-zone', function (e) {
      e.preventDefault();
      $(this).removeClass('dragover');
    });

    $(document).on('drop', '.efpp-drop-zone', function (e) {
      e.preventDefault();
      $(this).removeClass('dragover');

      const wrapper = $(this).closest('.efpp-featured-image-wrapper');
      const files = e.originalEvent.dataTransfer.files;

      Array.from(files).forEach(file => {
        if (!file.type.match('image.*')) return;

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
              appendImage(wrapper, res.data.url);
              updateInputs(wrapper);
            }
          }
        });
      });
    });
  });
})(jQuery);
