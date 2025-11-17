jQuery(document).ready(function($) {
    // Obsługa kliknięcia w link "Sprawdź aktualizacje"
    $(document).on('click', 'a.alex-efpp-check-update', function(e) {
        e.preventDefault();
        
        const $link = $(this);
        const originalText = $link.text();
        const url = $link.attr('href');
        
        // Zmień tekst na "Sprawdzam..."
        $link.text('Sprawdzam...').css('pointer-events', 'none');
        
        // Wyciągnij nonce z URL
        const urlParams = new URLSearchParams(url.split('?')[1]);
        const nonce = urlParams.get('nonce');
        
        // Użyj ajaxurl jeśli dostępny (WordPress admin), w przeciwnym razie użyj URL z linku
        const ajaxUrl = (typeof ajaxurl !== 'undefined' ? ajaxurl : 
                        (typeof alexEfppAjax !== 'undefined' ? alexEfppAjax.ajaxurl : 
                        url.split('?')[0]));
        
        // Wyślij żądanie AJAX
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'alex_efpp_check_update',
                nonce: nonce
            },
            success: function(response) {
                if (response.success) {
                    // Pokaż komunikat sukcesu
                    showNotice(response.data.message, 'success');
                    
                    // Jeśli jest dostępna aktualizacja, odśwież stronę po 2 sekundach
                    if (response.data.latest_version && 
                        response.data.current_version !== response.data.latest_version) {
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    }
                } else {
                    showNotice(response.data.message || 'Wystąpił błąd podczas sprawdzania aktualizacji.', 'error');
                    $link.text(originalText).css('pointer-events', 'auto');
                }
            },
            error: function() {
                showNotice('Wystąpił błąd podczas sprawdzania aktualizacji.', 'error');
                $link.text(originalText).css('pointer-events', 'auto');
            }
        });
    });
    
    /**
     * Wyświetla powiadomienie WordPress
     */
    function showNotice(message, type) {
        // Usuń poprzednie powiadomienia
        $('.alex-efpp-update-notice').remove();
        
        // Utwórz nowe powiadomienie
        const notice = $('<div class="alex-efpp-update-notice notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
        
        // Dodaj na górze strony
        $('.wrap h1').first().after(notice);
        
        // Dodaj funkcjonalność zamykania
        notice.on('click', '.notice-dismiss', function() {
            notice.fadeOut();
        });
        
        // Automatycznie zamknij po 5 sekundach dla sukcesu
        if (type === 'success') {
            setTimeout(function() {
                notice.fadeOut();
            }, 5000);
        }
        
        // Przewiń do góry, aby pokazać powiadomienie
        $('html, body').animate({
            scrollTop: 0
        }, 300);
    }
});

