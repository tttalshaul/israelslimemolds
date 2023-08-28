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
                                    return { fillColor: color, fillOpacity: 0.3, color: 'black', weight: 1 };
                                }
                            }).addTo(map);

                            polygon.bindPopup(area.name + '<br>' + 'כמה נמצאו: ' + (area.taxonCount ? area.taxonCount : 0));
                        });
                    } else {
                        console.error('Error fetching map data: ' + response.data);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('AJAX error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        }
        // else {
        //     // Fetch the countries that have the specified taxon
        //     var countriesUrl = 'https://www.inaturalist.org/places.json?taxon=' + taxon_id + '&place_type=country';
        //     $.getJSON(countriesUrl, function(countries) {
        //         countries.forEach(function(country) {
        //             // Fetch the taxon counts for each country
        //             var speciesCountsUrl = 'https://api.inaturalist.org/v1/observations/species_counts?taxon_id=' + taxon_id + '&place_id=' + country.id;
        //             $.getJSON(speciesCountsUrl, function(species_counts) {
        //                 $.getJSON("https://datahub.io/core/geo-countries/r/countries.geojson", function(countries_geojson) {
        //                     var count = species_counts.results[0].count;

        //                 }
        //                 var count = response.results[0].count;

        //                 // Calculate the tile coordinates for the country
        //                 var lat = country.latitude;
        //                 var lon = country.longitude;
        //                 var zoom = 5;
        // //                 var n = Math.pow(2, zoom);
        // //                 var xtile = n * ((country.longitude + 180) / 360);
        //                 var xtile = Math.floor((lon+180)/360*Math.pow(2,zoom));
        // //                 var lat_rad = country.latitude * Math.PI / 180;
        // //                 var ytile = n * (1 - (Math.log(Math.tan(lat_rad) + 1 / Math.cos(lat_rad)) / Math.PI)) / 2;
        //                 var ytile = Math.floor((1-Math.log(Math.tan(lat*Math.PI/180) + 1/Math.cos(lat*Math.PI/180))/Math.PI)/2 *Math.pow(2,zoom));

        //                 // Create the map tile URL
        //                 var tileUrl = 'https://api.inaturalist.org/v1/places/' + country.id + '/zoom/' + zoom + '/' + xtile + '/' + ytile + '.png';

        //                 // Create a marker with a custom icon
        //                 var markerIcon = L.icon({
        //                     iconUrl: tileUrl,
        //                     iconSize: [32, 32],
        //                     iconAnchor: [16, 16]
        //                 });

        //                 var marker = L.marker([country.latitude, country.longitude], {
        //                     icon: markerIcon
        //                 }).addTo(map);

        //                 // Determine the color based on the count
        //                 var color;
        //                 if (count <= 10) {
        //                     color = '#66ff66'; // Light green
        //                 } else if (count <= 100) {
        //                     color = '#33cc33'; // Medium green
        //                 } else {
        //                     color = '#009900'; // Dark green
        //                 }

        //                 // Create a circle marker for the country
        //                 L.circleMarker([country.latitude, country.longitude], {
        //                     radius: 5,
        //                     fillColor: color,
        //                     fillOpacity: 1,
        //                     color: '#000',
        //                     weight: 1
        //                 }).addTo(map);
        //             });
        //        });
        //     });

        //     // Create the Leaflet map
        //     var map = L.map(mapContainer).setView([0, 0], 2);

        //     // Add a tile layer to the map
        //     L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        //         attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors',
        //         maxZoom: 18
        //     }).addTo(map);
        // }
    });
});