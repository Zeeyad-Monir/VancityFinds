$(document).ready(function() {
    // Function to load parks based on search and filters
    function loadParks() {
        // Get selected category value from the URL or filters
        let category = window.location.search.indexOf('category') !== -1 ? 
            new URLSearchParams(window.location.search).get('category') : '';  // Get category from URL if available
        
        $.ajax({
            url: 'parks.php',  // Same page
            method: 'GET',
            data: $('#filter-form').serialize() + '&category=' + category,  // Include the category in the data
            success: function(response) {
                // Update the parks grid with the response (HTML)
                $('#parks-grid').html($(response).find('#parks-grid').html());
            }
        });
    }

    // Trigger AJAX request on form submit
    $('#filter-form').on('submit', function(event) {
        event.preventDefault();  // Prevent page reload
        loadParks();  // Reload parks based on filters
    });

    // Initial load with selected filters
    loadParks();
});