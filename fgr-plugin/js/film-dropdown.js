jQuery(document).ready(function($) {
    // Log that the script is running
    console.log('Film dropdown script is running');

    // Filter films based on user input
    $('#film_filter').on('input', function() {
        var filterValue = $(this).val().toLowerCase();
        if (filterValue === '') {
            $('#film_dropdown label').show();
        } else {
            $('#film_dropdown label').each(function() {
                var filmTitle = $(this).text().toLowerCase();
                if (filmTitle.includes(filterValue)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
    });
});
