(function($) {
    elementor.hooks.addAction('panel/open_editor/widget', function(panel, model, view) {
        const widgetType = model.get('widgetType');
        if (widgetType !== 'form') return;

        const tryRenderHint = (retries = 20) => {
            //console.log('[EFPP] tryRenderHint called. Retries left:', retries);

            const settings = model.get('settings');
            const formFields = settings?.get('form_fields'); // Backbone.Collection
            const efppTitleControl = view.$el.find('[data-setting="alex_efpp_post_title_field"]');

            //console.log('[EFPP] formFields:', formFields);
            //console.log('[EFPP] formFields.models:', formFields?.models);
            //console.log('[EFPP] efppTitleControl found:', efppTitleControl.length > 0);

            if (!efppTitleControl.length || !formFields || !formFields.models?.length) {
                if (retries > 0) {
                    setTimeout(() => tryRenderHint(retries - 1), 300);
                }
                return;
            }

            if ($('#alex-efpp-hint').length) return;

            let list = '<ul style="margin-top: 5px; font-size: 11px; line-height: 1.5;">';
            formFields.models.forEach(f => {
                const attr = f.attributes || {};
                const id = attr.custom_id || attr.field_id;
                const label = attr.field_label || id;
                if (id) list += `<li><code style="color:#ddd;">${id}</code> – <span style="color:#ccc;">${label}</span></li>`;
            });
            list += '</ul>';

            efppTitleControl
                .closest('.elementor-control-content')
                .append(`
                    <div id="alex-efpp-hint" style="
                        margin-top: 8px;
                        background: #1e1e1e;
                        padding: 10px;
                        border: 1px solid #333;
                        border-radius: 4px;
                        color: #ccc;
                        font-size: 12px;
                    ">
                        <strong style="color:#fff;">Available fields (IDs):</strong>${list}
                    </div>
                `);
        };

        tryRenderHint();
        //console.log('[EFPP] admin-hint.js loaded');
    });
})(jQuery);
