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

    function showError(wrapper, message) {
      const errorBox = wrapper.find('.efpp-error');
      errorBox.text(message).fadeIn();
      setTimeout(() => errorBox.fadeOut(), 5000);
    }

    // Dodawanie nowego obrazka do listy
    function appendImage(wrapper, url) {
      const maxImages = parseInt(wrapper.data('limit')) || 12;
      const currentCount = wrapper.find('.efpp-image-item').length;

      if (currentCount >= maxImages) {
        showError(wrapper, `Można dodać maksymalnie ${maxImages} zdjęć.`);
        return;
      }

      const imageItem = $(`
        <li class="efpp-image-item">
          <img src="${url}" />
          <button type="button" class="efpp-remove-image">×</button>
        </li>
      `);

      wrapper.find('.efpp-image-list').append(imageItem);
      wrapper.find('.efpp-error').fadeOut().text('');

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
    const gallery = images;

    const featuredInput = wrapper.find('input.efpp-featured-input');
    const galleryInput = wrapper.find('input.efpp-gallery-input');

    // 🔍 Logowanie do debugowania
    console.group('[EFPP] Gallery Update');
    console.log('🖼 Wszystkie zdjęcia:', images);
    console.log('⭐ Featured image:', featured);
    console.log('🖼 Gallery:', gallery);
    console.log('✅ Featured input:', featuredInput.attr('name'));
    console.log('✅ Gallery input:', galleryInput.attr('name'));
    console.groupEnd();

    // Ustaw wartości
    if (featuredInput.length) {
      featuredInput.val(featured);
    }
    if (galleryInput.length) {
      galleryInput.val(gallery.join(','));
    }
    wrapper.find('.efpp-error').fadeOut().text('');

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
      const allowedTypes = (wrapper.data('types') || 'jpg,jpeg,png,webp').split(',').map(t => t.trim().toLowerCase());
      const maxSizeMB = parseFloat(wrapper.data('max-size')) || 5;

      Array.from(files).forEach(file => {
        const ext = file.name.split('.').pop().toLowerCase();
        const sizeMB = file.size / 1024 / 1024;

        if (!allowedTypes.includes(ext)) {
          showError(wrapper, `Plik ${file.name} ma niedozwolony format (${ext}). Dozwolone: ${allowedTypes.join(', ')}`);
          return;
        }

        if (sizeMB > maxSizeMB) {
          showError(wrapper, `Plik ${file.name} jest za duży (${sizeMB.toFixed(2)} MB). Limit: ${maxSizeMB} MB.`);
          return;
        }

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
