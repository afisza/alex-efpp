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
    updateCheckedState();
    $(document).on('change', '.elementor-field-option input[type="checkbox"], .elementor-field-option input[type="radio"]', function () {
      updateCheckedState();
    });
  });

  // Live preview support
  const waitForElementorFrontend = setInterval(function () {
    if (window.elementorFrontend && window.elementorFrontend.hooks) {
      clearInterval(waitForElementorFrontend);
      elementorFrontend.hooks.addAction('frontend/element_ready/form.default', function ($scope) {
        updateCheckedState($scope);
      });
    }
  }, 200);

})(jQuery); // ← TO BYŁO BRAKUJĄCE!
