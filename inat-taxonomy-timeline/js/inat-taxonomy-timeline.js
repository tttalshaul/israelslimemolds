// jQuery(document).ready(function($) {
//     // Get the taxon ID from the data attribute of the timeline rectangle
//     var taxonID = $('.inat-taxonomy-timeline').data('taxon-id');

//     // Make an AJAX request to fetch the data on page load
//     $.ajax({
//         type: 'GET',
//         url: ajax_object.ajax_url, // Use the WordPress AJAX endpoint
//         data: {
//             action: 'get_inat_data', // Create a custom AJAX action
//             taxon_id: taxonID,
//         },
//         success: function(response) {
//             // Process the response and format the HTML of the timeline
//             // You can format the HTML here based on the response data

//             // Define the color ranges
//             var colorRanges = {
//                 'white': { label: 'No reports', minPercentage: 0, maxPercentage: 0 },
//                 'lightgreen': { label: 'Few reports', minPercentage: 0, maxPercentage: 25 },
//                 'green': { label: 'Average reports', minPercentage: 25, maxPercentage: 75 },
//                 'darkgreen': { label: 'Many reports', minPercentage: 75, maxPercentage: 100 }
//             };

//             var months = response.data;
//             var countTotal = 0;
//             var timelineHTML = '';

//             // Sum total
//             for (var month = 1; month <= 12; month++) {
//                 countTotal += months[month];
//             }

//             // Loop through each month to build HTML
//             for (var month = 1; month <= 12; month++) {
//                 var hebrewMonthName = getMonthNameHebrew(month);

//                 // Calculate the percentage based on the maximum count
//                 var percentage = (months[month] / countTotal) * 100;

//                 // Determine the color based on the percentage
//                 var color = '';
//                 var label = '';
//                 for (var rangeColor in colorRanges) {
//                     if (
//                         percentage >= colorRanges[rangeColor].minPercentage &&
//                         percentage <= colorRanges[rangeColor].maxPercentage
//                     ) {
//                         color = rangeColor;
//                         label = colorRanges[rangeColor].label;
//                         break;
//                     }
//                 }

//                 var firstOrLast = '';
//                 if (month === 1) {
//                     firstOrLast = 'timeline-item-first';
//                 } else if (month === 12) {
//                     firstOrLast = 'timeline-item-last';
//                 }

//                 // Add the timeline item to the HTML
//                 timelineHTML += '<div class="timeline-item timeline-item-' + color + ' ' + firstOrLast + '" data-month="' + hebrewMonthName + '" data-count="' + months[month] + '">';
//                 timelineHTML += '<div class="timeline-label">' + hebrewMonthName + '</div>';
//                 timelineHTML += '</div>';
//             }

//             // Update the content of the timeline rectangle with the formatted HTML
//             $('.inat-taxonomy-timeline').html(timelineHTML);

//             $('.timeline-item').click(function(e) {
//                 var $this = $(this);
//                 var month = $this.data('month');
//                 var count = $this.data('count');

//                 // Remove existing popups
//                 $('.timeline-popup').remove();

//                 // Create popup
//                 var $popup = $('<div class="timeline-popup"></div>');
//                 var $popupContent = $('<div class="popup-content"></div>');
//                 var $popupMonth = $('<div class="popup-month">' + month + '</div>');
//                 var $popupCount = $('<div class="popup-count">כמה נמצאו: ' + count + '</div>');
//                 var $popupTip = $('<div class="popup-tip"></div>');

//                 // Append content to popup
//                 $popupContent.append($popupMonth, $popupCount);
//                 $popup.append($popupContent, $popupTip);

//                 // Append popup to rectangle
//                 $this.append($popup);

//                 // Position the popup
//                 var popupWidth = $popup.outerWidth();
//                 var popupHeight = $popup.outerHeight();
//                 var offsetX = popupWidth / 2;
//                 var offsetY = popupHeight + 6;
//                 $popup.css({
//                     top: e.pageY - offsetY,
//                     left: e.pageX - offsetX,
//                 });
//             });

//             $(document).on('click', function(event) {
//                 if (!$(event.target).closest('.timeline-item').length) {
//                     $('.timeline-item').find('.timeline-popup').remove();
//                 }
//             });
//         },
//         error: function() {
//             // Handle errors if the AJAX request fails
//             console.error('AJAX request failed');
//         }
//     });
// });

function fillTaxonomyTimeline($, areas) {
    // Process the response and format the HTML of the timeline
    // You can format the HTML here based on the response data

    // Define the color ranges
    var colorRanges = {
        'white': { label: 'No reports', minPercentage: 0, maxPercentage: 0 },
        'lightgreen': { label: 'Few reports', minPercentage: 0, maxPercentage: 25 },
        'green': { label: 'Average reports', minPercentage: 25, maxPercentage: 75 },
        'darkgreen': { label: 'Many reports', minPercentage: 75, maxPercentage: 100 }
    };

    var months = {
        1: 0,
        2: 0,
        3: 0,
        4: 0,
        5: 0,
        6: 0,
        7: 0,
        8: 0,
        9: 0,
        10: 0,
        11: 0,
        12: 0,
    };
    areas.forEach(function(area) {
        for (var keyMonth in area.taxonCountByMonth) {
            months[parseInt(keyMonth)] += area.taxonCountByMonth[keyMonth];
        }
    });
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

    // Initialize dataset for each area
    var datasets = areas.map(area => {
        var counts = Object.keys(months).map(
            month => area.taxonCountByMonth[month.toString().padStart(2, '0')] || 0
        );
        return {
            label: area.name,
            data: counts,
            fill: true, // For area graph
            borderColor: getRandomColor(),
            backgroundColor: getRandomColor(0.3) // Lighter fill color
        };
    });

    // Create the chart
    var chartCanvas = document.getElementById('inat-taxonomy-timeline-canvas');
    if (typeof chartCanvas !== 'undefined')
    {
        chartCanvas = chartCanvas.getContext('2d');
        var myChart = new Chart(chartCanvas, {
            type: 'bar', // Change to 'line' for area graph, 'bar' for bar graph
            data: {
                labels: Object.keys(months).map(month => month.toString().padStart(2, '0')), // Format months
                datasets: datasets
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'איפה ניתן למצוא את המין הזה בכל חודש?'
                    }
                },
                scales: {
                    x: {
                        stacked: true,
                        title: {
                            display: true,
                            text: 'חודש'
                        }
                    },
                    y: {
                        stacked: true,
                        title: {
                            display: true,
                            text: 'מספר דיווחים'
                        }
                    }
                }
            }
        });
    }
}

// Function to generate random color
function getRandomColor(alpha = 1) {
    var r = Math.floor(Math.random() * 255);
    var g = Math.floor(Math.random() * 255);
    var b = Math.floor(Math.random() * 255);
    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
}

// Helper function to get Hebrew month name
function getMonthNameHebrew(month) {
    var hebrewMonthNames = [
        'ינואר', 'פברואר', 'מרץ', 'אפריל', 'מאי', 'יוני',
        'יולי', 'אוגוסט', 'ספטמבר', 'אוקטובר', 'נובמבר', 'דצמבר'
    ];

    return hebrewMonthNames[month - 1] || '';
}
