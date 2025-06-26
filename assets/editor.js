jQuery(document).ready(function($) {
    $(document)
        .off('change.efpp', '.efpp-remote-render select, .efpp-remote-render input') // namespaced for safety
        .on('change.efpp', '.efpp-remote-render select, .efpp-remote-render input', function() {
            elementor.getPanelView().currentPageView.model.renderRemoteServer();
        });

});
