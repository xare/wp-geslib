jQuery(document).ready(function($) {
    // Handle pagination links
    $('.pagination-container a').on('click', function(e) {
        e.preventDefault();
        var page = $(this).attr('href').split('=')[1];

        // Send AJAX request to retrieve the paginated results
        $.ajax({
            url: ajaxurl, // WordPress AJAX URL
            type: 'POST',
            data: {
                action: 'geslib_pagination',
                page: page
            },
            success: function(response) {
                // Update the table content with the paginated results
                $('.table-container').html(response);
            },
            error: function(xhr, status, error) {
                console.log(xhr.responseText);
            }
        });
    });
});
