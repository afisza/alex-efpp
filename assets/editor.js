jQuery(document).off('change.efpp', '.efpp-remote-render select') // namespaced for safety
                    .on('change.efpp', '.efpp-remote-render select', function() {
                        elementor.getPanelView().currentPageView.model.renderRemoteServer();
                    });