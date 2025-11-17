jQuery(document).ready(function ($) {
  $(document).on('elementor-pro/forms/ajax:success', function (event, id, responseData, $form) {

    let $targetForm = $(`form[name="${id}"]`);
    if (!$targetForm.length && $form?.length) {
      $targetForm = $form;
    }

    if (!$targetForm.length) {
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
