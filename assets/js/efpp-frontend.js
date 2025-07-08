console.log('[EFPP] Script loaded');

(function ($) {
  function updateCheckedState($root = $(document)) {
    $root.find('.elementor-field-option input[type="checkbox"], .elementor-field-option input[type="radio"]').each(function () {
      const $input = $(this);
      const $wrapper = $input.closest('.elementor-field-option');
      if ($input.is(':checked')) {
        $wrapper.addClass('efpp-checked');
      } else {
        $wrapper.removeClass('efpp-checked');
      }
    });
  }

  $(document).ready(function () {
    console.log('[EFPP] Document ready');
    updateCheckedState();
    $(document).on('change', '.elementor-field-option input[type="checkbox"], .elementor-field-option input[type="radio"]', function () {
      updateCheckedState();
    });
  });

  // Live preview support
  const waitForElementorFrontend = setInterval(function () {
    if (window.elementorFrontend && window.elementorFrontend.hooks) {
      clearInterval(waitForElementorFrontend);
      console.log('[EFPP] Elementor hooks ready');
      elementorFrontend.hooks.addAction('frontend/element_ready/form.default', function ($scope) {
        console.log('[EFPP] Form ready in editor');
        updateCheckedState($scope);
      });
    }
  }, 200);

})(jQuery); // ← TO BYŁO BRAKUJĄCE!
