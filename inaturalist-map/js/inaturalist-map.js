jQuery(document).ready(function($) {
    // Get all map container elements
    var mapContainers = $('.inat-map');

    // Iterate over each map container
    mapContainers.each(function(i, mapContainer) {
        // Get the taxon ID from the shortcode attribute
        var taxon_id = mapContainer.dataset.taxonId;
        var place_id = mapContainer.dataset.placeId;

        var ajax_data = {
            action: 'inaturalist_map_get_data',
            taxon_id: taxon_id,
            place_id: place_id
        };

        if (place_id == 6815) {
            // AJAX request to fetch the map data
            $.ajax({
                url: inaturalist_map_params.ajax_url,
                type: 'GET',
                data: ajax_data,
                success: function(response) {
                    if (response.success) {
                        var areas = response.data;

                        // Create the map
                        var map = L.map(mapContainer).setView([31.4117257, 35.0818155], 7); // Set the initial map view

                        // Add the base tile layer
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors',
                            maxZoom: 18,
                        }).addTo(map);

                        // Define the colors for different report counts
                        var lightGreen = 'yellow'; // 'rgb(231,201,39)'; // '#A2FF8F';
                        var regularGreen = 'orange'; // 'rgb(180,187,70)'; // '#00B300';
                        var darkGreen = 'darkgreen'; // 'rgb(53,186,36)'; // '#008000';

                        // Calculate the range of taxon counts
                        var taxonCounts = areas.map(function(area) {
                            return area.taxonCount ? area.taxonCount : 0;
                        });
                        var minTaxonCount = Math.min.apply(null, taxonCounts);
                        var maxTaxonCount = Math.max.apply(null, taxonCounts);

                        // Function to determine the color based on taxon count
                        function getColor(taxonCount) {
                            if (taxonCount === 0) {
                                return regularGreen;
                            }

                            var range = maxTaxonCount - minTaxonCount;
                            var percentage = (taxonCount - minTaxonCount) / range;

                            if (percentage < 0.2) {
                                return lightGreen;
                            } else if (percentage > 0.8) {
                                return darkGreen;
                            } else {
                                return regularGreen;
                            }
                        }

                        // Add polygons for each area
                        areas.forEach(function(area) {
                            var polygon = L.geoJSON(area.geometry, {
                                style: function(feature) {
                                    var taxonCount = area.taxonCount ? area.taxonCount : 0;
                                    var color = getColor(taxonCount);

                                    return { 
                                        fill: true,
                                        fillColor: color,
                                        color: 'black', 
                                        fillOpacity: 0.3, 
                                        weight: 1,
                                    };
                                }
                            }).addTo(map);

                            polygon.bindPopup(area.name + '<br>' + 'כמה נמצאו: ' + (area.taxonCount ? area.taxonCount : 0));
                        });

                        if (typeof(fillTaxonomyTimeline) != 'undefined') {
                            fillTaxonomyTimeline($, areas);
                        }
                    } else {
                        console.error('Error fetching map data: ' + response.data);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('AJAX error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        }
    });
});