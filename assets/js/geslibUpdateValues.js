function updateStatistics() {
    jQuery.ajax({
        url: ajax_object.ajax_url,
        type: 'post',
        data: {
            action: 'get_geslib_statistics',
            nonce: ajax_object.nonce
        },
        success: function(response) {
            if(response.success) {
                // Update your DOM elements here
                const targets = [
                    'total-products',
                    'total-files',
                    'total-logs',
                    'total-lines',
                    'total-lines-queue',
                    'total-products-queue',
                    'queued-filename',
                    'geslib-log-logged',
                    'geslib-log-queued',
                    'geslib-log-processed',
                ];
                targets.forEach( target => {
                    let $element = jQuery( `[data-target="${target}"]` );
                    let $li = $element.closest('li'); // Finds the closest li ancestor of the element
                    $li.addClass( 'darker-background' );
                    $element.fadeOut( 400, function() {
                        $element.text( response.data[target] );
                        $element.fadeIn( 400 );
                        $li.removeClass( 'darker-background' );
                    });
                });
            }
        }
    });
}

// Update every 3 seconds
setInterval(updateStatistics, 8000);
