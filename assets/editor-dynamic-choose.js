jQuery(function ($) {
  $(document).on('focus', '.elementor-control[data-setting="field_group"] select', function () {
    const $select = $(this);
    console.log('EFPP: field_group select focused');

    if ($select.find('option').length > 1) return;

    const $sourceSelect = $('.elementor-control[data-setting="source_type"] select');
    const sourceType = $sourceSelect.val() || 'acf';
    console.log('EFPP: używamy sourceType =', sourceType);

    $.post(AlexEFPP.ajax_url, {
      action: 'alex_efpp_list_field_groups',
      _ajax_nonce: AlexEFPP.nonce,
      source_type: sourceType,
    }, function (res) {
      if (res.success) {
        console.log('EFPP: field groups loaded via AJAX', res.data);
        $select.empty().append(`<option value="">-- wybierz --</option>`);
        $.each(res.data, function (i, opt) {
          $select.append(`<option value="${opt.id}">${opt.text}</option>`);
        });
      } else {
        console.warn('EFPP: AJAX response error', res);
      }
    }).fail(function (xhr) {
      console.error('EFPP: AJAX failed', xhr.responseText);
    });
  });
});
