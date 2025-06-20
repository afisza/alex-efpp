(function($){
    function updateTaxonomyOptions(postType) {
        if (!postType) return;

        $.ajax({
            url: AlexEFPP.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'alex_efpp_get_taxonomies',
                post_type: postType,
                _ajax_nonce: AlexEFPP.nonce
            },
            success: function(response) {
                if (response.success && response.data) {
                    const $taxonomySelect = $('.elementor-control-alex_efpp_taxonomy select');

                    if ($taxonomySelect.length) {
                        const currentValue = $taxonomySelect.data('selected') || $taxonomySelect.val();

                        $taxonomySelect.empty();

                        $.each(response.data, function(slug, label) {
                            const $option = $('<option>', { value: slug, text: label });

                            if (slug === currentValue) {
                                $option.prop('selected', true);
                            }

                            $taxonomySelect.append($option);
                        });

                        $taxonomySelect.trigger('change');
                    }
                }
            },
            error: function(xhr) {
                console.warn('EFPP AJAX taxonomy fetch error:', xhr.responseText);
            }
        });
    }

    $(window).on('elementor:init', function () {
        setTimeout(function () {
            const $postTypeSelect = $('.elementor-control-alex_efpp_post_type select');
            const $taxonomySelect = $('.elementor-control-alex_efpp_taxonomy select');

            if ($postTypeSelect.length && $taxonomySelect.length) {
                // zapamiętanie wybranej opcji
                $taxonomySelect.data('selected', $taxonomySelect.val());

                updateTaxonomyOptions($postTypeSelect.val());
            }
        }, 500);

        // nasłuch zmiany typu wpisu
        $(document).on('change', '.elementor-control-alex_efpp_post_type select', function () {
            updateTaxonomyOptions($(this).val());
        });

        // aktualizacja data-selected przy zmianie taksonomii
        $(document).on('change', '.elementor-control-alex_efpp_taxonomy select', function () {
            $(this).data('selected', $(this).val());
        });
    });
})(jQuery);
