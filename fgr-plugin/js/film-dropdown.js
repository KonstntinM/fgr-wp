jQuery(document).ready(function($) {
    const $dropdown = $('#film_dropdown');
    const $selectedContainer = $('.selected-container');
    const $availableArea = $('#available_films_area');
    const $selectedArea = $('#selected_films_area');
    const $filterInput = $('#film_filter');
    const $noResults = $('#no-results');

    // Function to organize items on load and on change
    function organizeItems() {
        const $items = $('.film-item');

        $items.each(function() {
            const $checkbox = $(this).find('input[type="checkbox"]');
            if ($checkbox.is(':checked')) {
                $(this).appendTo($selectedContainer).addClass('is-selected');
            } else {
                $(this).appendTo($availableArea).removeClass('is-selected');
            }
        });

        // Show/Hide the "Selected" header based on content
        if ($selectedContainer.children().length > 0) {
            $selectedArea.show();
        } else {
            $selectedArea.hide();
        }
    }

    // Initial run to move already-saved items to the top
    organizeItems();

    // Handle clicking a checkbox
    $dropdown.on('change', 'input[type="checkbox"]', function() {
        organizeItems();
        // Clear filter after selection so user sees where the item went
        //$filterInput.val('').trigger('input');
    });

    // Filtering logic
    $filterInput.on('input', function() {
        const val = $(this).val().toLowerCase().trim();
        let visibleCount = 0;

        $('.film-item').each(function() {
            const text = $(this).text().toLowerCase();
            if (text.indexOf(val) > -1) {
                $(this).show();
                visibleCount++;
            } else {
                $(this).hide();
            }
        });

        visibleCount === 0 ? $noResults.show() : $noResults.hide();
    });
});