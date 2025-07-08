jQuery(function ($) {
  $(document).on('focus', '.elementor-repeater-row-controls select[data-setting="field_group"]', function () {
    console.log('EFPP: field_group select focused');

    const $select = $(this);

    // zapobiegaj dublowaniu
    if ($select.find('option').length > 1) return;

    const $sourceSelect = $select
      .closest('.elementor-repeater-row-controls')
      .find('select[data-setting="source_type"]');

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
    });
  });
});


