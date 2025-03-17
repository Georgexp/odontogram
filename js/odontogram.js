$(document).ready(function() {
    console.log("odontogram.js loaded");
    console.log("jQuery version: ", jQuery.fn.jquery);

    // Create an SVG container with SVG.js
    var draw = SVG().addTo('#odontogram-svg').size(1045, 690);

    // Load the SVG from the URL
    $.get('/interface/forms/odontogram/assets/odontogram.svg', function(svgData) {
        // Convert the SVG to a DOM element
        var svgDoc = new DOMParser().parseFromString(svgData, 'image/svg+xml');
        var svgElement = svgDoc.documentElement;

        // Add the SVG to the container
        draw.svg(svgData);

        // Access the groups
        var numbersLayer = draw.findOne('#Numbers');
        if (numbersLayer) {
            console.log("Layer #Numbers found");
            var fdi = numbersLayer.findOne('#FDI');
            var universal = numbersLayer.findOne('#Universal');
            var palmer = numbersLayer.findOne('#Palmer');
            console.log("FDI Groups: ", fdi ? 1 : 0);
            console.log("Universal Groups: ", universal ? 1 : 0);
            console.log("Palmer Groups: ", palmer ? 1 : 0);

            // Hide all initially
            if (fdi) fdi.hide();
            if (universal) universal.hide();
            if (palmer) palmer.hide();
        } else {
            console.log("Layer #Numbers NOT found");
        }

        // Change numbering system
        $('#numbering_system').change(function() {
            var system = $(this).val();
            console.log("Selected system: " + system);

            if (fdi) fdi.hide();
            if (universal) universal.hide();
            if (palmer) palmer.hide();

            var selectedGroup = numbersLayer ? numbersLayer.findOne('#' + system) : null;
            if (selectedGroup) {
                selectedGroup.show();
                console.log("Displaying: #" + system);
            } else {
                console.log("Group #" + system + " not found");
            }

            // Save preference
            $.ajax({
                url: '/interface/forms/odontogram/new.php',
                type: 'POST',
                data: { system: system },
                success: function(response) {
                    console.log("Preference saved: " + system);
                },
                error: function(xhr, status, error) {
                    console.error("Error saving preference: " + error);
                }
            });
        });

        console.log("Restored system: " + defaultSystem);
        $('#numbering_system').val(defaultSystem).trigger('change');
    }, 'text').fail(function(jqXHR, textStatus, errorThrown) {
        console.error("Error loading the SVG:", textStatus, errorThrown);
    });
});
