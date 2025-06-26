jQuery(document).ready(function ($) {
  console.log('[EFPP] JS is loaded');

  $(document).on('elementor-pro/forms/ajax:success', function (event, id, responseData, $form) {
    console.group('[EFPP] AJAX: SUCCESS HOOK');
    console.log('event:', event);
    console.log('form name (id):', id);
    console.log('responseData:', responseData);
    console.log('responseData.message:', responseData?.message);
    console.log('responseData.data:', responseData?.data);
    console.groupEnd();

    let $targetForm = $(`form[name="${id}"]`);
    if (!$targetForm.length && $form?.length) {
      $targetForm = $form;
    }

    if (!$targetForm.length) {
      console.warn('[EFPP] Nie znaleziono formularza!');
      return;
    }

    let container = $targetForm.find('.efpp-messages');
    if (!container.length) {
      const submitField = $targetForm.find('.elementor-field-type-submit');
      container = $('<div class="efpp-messages" style="display:none;"></div>');
      submitField.after(container);
    }

    const messages = responseData?.data?.message || responseData?.message || '✔️ Sukces';
    const redirectUrl = responseData?.data?.redirect_url;

    container.html(`<div class="efpp-message">${messages}</div>`);
    container.fadeIn(200);

    if (redirectUrl) {
      setTimeout(() => {
        container.fadeOut(300, () => {
          window.location.href = redirectUrl;
        });
      }, 2000);
    } else {
      setTimeout(() => {
        container.fadeOut();
      }, 5000);
    }
  });
});
