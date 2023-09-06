jQuery(document).ready(function($) {
    // Get the taxon ID from the data attribute of the timeline rectangle
    var taxonID = $('.inat-taxonomy-timeline').data('taxon-id');

    // Make an AJAX request to fetch the data on page load
    $.ajax({
        type: 'GET',
        url: ajax_object.ajax_url, // Use the WordPress AJAX endpoint
        data: {
            action: 'get_inat_data', // Create a custom AJAX action
            taxon_id: taxonID,
        },
        success: function(response) {
            // Process the response and format the HTML of the timeline
            // You can format the HTML here based on the response data

            // Define the color ranges
            var colorRanges = {
                'white': { label: 'No reports', minPercentage: 0, maxPercentage: 0 },
                'lightgreen': { label: 'Few reports', minPercentage: 0, maxPercentage: 25 },
                'green': { label: 'Average reports', minPercentage: 25, maxPercentage: 75 },
                'darkgreen': { label: 'Many reports', minPercentage: 75, maxPercentage: 100 }
            };

            var months = response.data;
            var countTotal = 0;
            var timelineHTML = '';

            // Sum total
            for (var month = 1; month <= 12; month++) {
                countTotal += months[month];
            }

            // Loop through each month to build HTML
            for (var month = 1; month <= 12; month++) {
                var hebrewMonthName = getMonthNameHebrew(month);

                // Calculate the percentage based on the maximum count
                var percentage = (months[month] / countTotal) * 100;

                // Determine the color based on the percentage
                var color = '';
                var label = '';
                for (var rangeColor in colorRanges) {
                    if (
                        percentage >= colorRanges[rangeColor].minPercentage &&
                        percentage <= colorRanges[rangeColor].maxPercentage
                    ) {
                        color = rangeColor;
                        label = colorRanges[rangeColor].label;
                        break;
                    }
                }

                var firstOrLast = '';
                if (month === 1) {
                    firstOrLast = 'timeline-item-first';
                } else if (month === 12) {
                    firstOrLast = 'timeline-item-last';
                }

                // Add the timeline item to the HTML
                timelineHTML += '<div class="timeline-item timeline-item-' + color + ' ' + firstOrLast + '" data-month="' + hebrewMonthName + '" data-count="' + months[month] + '">';
                timelineHTML += '<div class="timeline-label">' + hebrewMonthName + '</div>';
                timelineHTML += '</div>';
            }

            // Update the content of the timeline rectangle with the formatted HTML
            $('.inat-taxonomy-timeline').html(timelineHTML);

            $('.timeline-item').click(function(e) {
                var $this = $(this);
                var month = $this.data('month');
                var count = $this.data('count');

                // Remove existing popups
                $('.timeline-popup').remove();

                // Create popup
                var $popup = $('<div class="timeline-popup"></div>');
                var $popupContent = $('<div class="popup-content"></div>');
                var $popupMonth = $('<div class="popup-month">' + month + '</div>');
                var $popupCount = $('<div class="popup-count">כמה נמצאו: ' + count + '</div>');
                var $popupTip = $('<div class="popup-tip"></div>');

                // Append content to popup
                $popupContent.append($popupMonth, $popupCount);
                $popup.append($popupContent, $popupTip);

                // Append popup to rectangle
                $this.append($popup);

                // Position the popup
                var popupWidth = $popup.outerWidth();
                var popupHeight = $popup.outerHeight();
                var offsetX = popupWidth / 2;
                var offsetY = popupHeight + 6;
                $popup.css({
                    top: e.pageY - offsetY,
                    left: e.pageX - offsetX,
                });
            });

            $(document).on('click', function(event) {
                if (!$(event.target).closest('.timeline-item').length) {
                    $('.timeline-item').find('.timeline-popup').remove();
                }
            });
        },
        error: function() {
            // Handle errors if the AJAX request fails
            console.error('AJAX request failed');
        }
    });
});

// Helper function to get Hebrew month name
function getMonthNameHebrew(month) {
    var hebrewMonthNames = [
        'ינואר', 'פברואר', 'מרץ', 'אפריל', 'מאי', 'יוני',
        'יולי', 'אוגוסט', 'ספטמבר', 'אוקטובר', 'נובמבר', 'דצמבר'
    ];

    return hebrewMonthNames[month - 1] || '';
}
