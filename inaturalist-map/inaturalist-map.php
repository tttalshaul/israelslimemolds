<?php
/*
Plugin Name: iNaturalist Map
Plugin URI: http://slimemoldsisrael.byethost7.com
Description: Displays areas where a specific taxon ID is reported to be found on iNaturalist.
Version: 1.0
Author: Tal Shaul using ChatGPT
Author URI: http://slimemoldsisrael.byethost7.com
*/

// Plugin code goes here
function inaturalist_map_enqueue_scripts() {
    // Enqueue Leaflet JS library
    wp_enqueue_script('leaflet', 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/leaflet.min.js', array(), '1.7.1', true);

    // Enqueue Leaflet CSS file
    wp_enqueue_style('leaflet-css', 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/leaflet.min.css', array(), '1.7.1');

    // Enqueue custom CSS file
    wp_enqueue_style('inaturalist-map-css', plugin_dir_url(__FILE__) . 'style/inaturalist-map.css', array(), '1.0');

    // Enqueue custom JavaScript file
    wp_enqueue_script('inaturalist-map', plugin_dir_url(__FILE__) . 'js/inaturalist-map.js', array('jquery'), '1.0', true);

    // Pass necessary data to JavaScript
    wp_localize_script('inaturalist-map', 'inaturalist_map_params', array(
        'ajax_url' => admin_url('admin-ajax.php')
    ));
}
add_action('wp_enqueue_scripts', 'inaturalist_map_enqueue_scripts');

function inaturalist_map_get_data($query) {
    $taxon_id = isset($_GET['taxon_id']) ? intval($_GET['taxon_id']) : 0;
    $place_id = isset($_GET['place_id']) ? intval($_GET['place_id']) : 0;

    // Make a request to the iNaturalist API to get the data
    if ($place_id == 6815) {
        $api_url = "https://api.inaturalist.org/v1/observations?taxon_id={$taxon_id}&place_id={$place_id}&per_page=200";
    // else {
    //     $api_url = "https://api.inaturalist.org/v1/observations?taxon_id={$taxon_id}&per_page=200";
    // }
        $response = wp_remote_get($api_url);

        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            // Load the areas GeoJSON data from a file
            // $geojson_file = plugin_dir_path(__FILE__) . 'israel_districts.json';
            $geojson_file = plugin_dir_path(__FILE__) . 'israel_areas.json';
            // else {
            //     $geojson_file = plugin_dir_path(__FILE__) . 'countries_borders.json';
            // }

            $AreasGeoJSON = file_get_contents($geojson_file);
            try {
                $Areas = json_decode($AreasGeoJSON, true, 512, JSON_INVALID_UTF8_IGNORE | JSON_THROW_ON_ERROR);
            }
            catch (Exception $e) {
                wp_send_json_error('Message: ' .$e->getMessage());
            }

            // Assign the areas polygons to the $areas array
            $areas = $Areas['features'];
            $areas_with_taxon = array();

            // Extract necessary data from the API response
            foreach ($data['results'] as $result) {
                if (isset($result['geojson']) && $result['geojson']['type'] === 'Point') {
                    $geometry = $result['geojson']['coordinates'];
                    $polygon = findContainingPolygon($geometry, $areas);

                    if ($polygon !== null) {
                        // Increment the count for the taxon ID in the polygon
                        $area_name = $polygon['name'];
                        if (!isset($areas_with_taxon[$area_name])) {
                            $areas_with_taxon[$area_name] = $polygon;
                            $areas_with_taxon[$area_name]['taxonCount'] = 1;
                        }
                        else {
                            $areas_with_taxon[$area_name]['taxonCount']++;
                        }
                    }
                }
            }
            wp_send_json_success(array_values($areas_with_taxon));
        } else {
            $error_message = is_wp_error($response) ? $response->get_error_message() : 'Failed to fetch data from iNaturalist API.';
            wp_send_json_error($error_message);
        }
    }
    else {
        wp_send_json_success(json_encode(array('place_id' => $place_id)));
    }
}

function findContainingPolygon($point, &$areas) {
    foreach ($areas as &$area) {
        if (isPointInsidePolygon($point, $area['geometry'])) {
            return $area;
        }
    }

    return null;
}

function isPointInsidePolygon($point, $polygon) {
    if ($polygon['type'] === 'Polygon') {
        return isPointInsideSinglePolygon($point, $polygon['coordinates'][0]);
    } elseif ($polygon['type'] === 'MultiPolygon') {
        foreach ($polygon['coordinates'] as $subPolygon) {
            if (isPointInsideSinglePolygon($point, $subPolygon[0])) {
                return true;
            }
        }
    }

    return false;
}

function isPointInsideSinglePolygon($point, $polygon) {
    $lat = $point[1];
    $lon = $point[0];

    $inside = false;
    $numVertices = count($polygon);

    for ($i = 0, $j = $numVertices - 1; $i < $numVertices; $j = $i++) {
        $vertex1 = $polygon[$i];
        $vertex2 = $polygon[$j];

        $lon1 = $vertex1[0];
        $lat1 = $vertex1[1];
        $lon2 = $vertex2[0];
        $lat2 = $vertex2[1];

        if (($lat1 > $lat) !== ($lat2 > $lat) &&
            ($lon < ($lon2 - $lon1) * ($lat - $lat1) / ($lat2 - $lat1) + $lon1)) {
            $inside = !$inside;
        }
    }

    return $inside;
}

add_action('wp_ajax_inaturalist_map_get_data', 'inaturalist_map_get_data');
add_action('wp_ajax_nopriv_inaturalist_map_get_data', 'inaturalist_map_get_data');

function inaturalist_map_shortcode($atts) {
    if ( !is_home() && !is_category()) {
        $atts = shortcode_atts(array(
            'taxon_id' => '56273', // Default taxon ID if not specified in the shortcode
            'place_id' => '0',
        ), $atts);

        ob_start();
        if ($atts['place_id'] == "6815")
        {
            ?>
            <div class="inat-map" id="inaturalist-map-<?php echo esc_attr($atts['place_id']); ?>" style="height: 450px;" data-taxon-id="<?php echo esc_attr($atts['taxon_id']); ?>" data-place-id="<?php echo esc_attr($atts['place_id']); ?>"></div>
            <?php
        }
        else {
            ?>
            <iframe id="inat-iframe" src="https://www.inaturalist.org/taxa/map?taxa=<?php echo esc_attr($atts['taxon_id']); ?>">
            </iframe>
            <?php
        }
        return ob_get_clean();
    }
}
add_shortcode('inaturalist_map', 'inaturalist_map_shortcode');

// AJAX callback to fetch the countries with the specified taxon
function inaturalist_map_get_countries() {
    $taxonId = $_GET['taxon_id'];

    $countriesUrl = 'https://www.inaturalist.org/places.json?taxon=' . $taxonId . '&place_type=country';

    $response = wp_remote_get($countriesUrl);

    if (!is_wp_error($response) && $response['response']['code'] === 200) {
        $countries = json_decode($response['body'], true);

        // Send the countries as JSON response
        wp_send_json($countries);
    }

    wp_send_json_error('Failed to fetch countries');
}
add_action('wp_ajax_get_countries', 'inaturalist_map_get_countries');
add_action('wp_ajax_nopriv_get_countries', 'inaturalist_map_get_countries');

// AJAX callback to fetch the taxon count for a specific country
function inaturalist_map_get_species_count() {
    $taxonId = $_GET['taxon_id'];
    $placeId = $_GET['place_id'];

    $speciesCountUrl = 'https://api.inaturalist.org/v1/observations/species_counts?taxon_id=' . $taxonId . '&place_id=' . $placeId;

    $response = wp_remote_get($speciesCountUrl);

    if (!is_wp_error($response) && $response['response']['code'] === 200) {
        $count = json_decode($response['body'], true)['total_results'];

        // Send the count as JSON response
        wp_send_json($count);
    }

    wp_send_json_error('Failed to fetch species count');
}
add_action('wp_ajax_get_species_count', 'inaturalist_map_get_species_count');
add_action('wp_ajax_nopriv_get_species_count', 'inaturalist_map_get_species_count');

// AJAX callback to fetch the map tile for a specific country
function inaturalist_map_get_tile() {
    $placeId = $_GET['place_id'];
    $zoom = $_GET['zoom'];
    $xtile = $_GET['xtile'];
    $ytile = $_GET['ytile'];

    $tileUrl = 'https://api.inaturalist.org/v1/places/' . $placeId . '/zoom/' . $zoom . '/' . $xtile . '/' . $ytile . '.png';

    $response = wp_remote_get($tileUrl);

    if (!is_wp_error($response) && $response['response']['code'] === 200) {
        $image = $response['body'];
        $type = wp_remote_retrieve_header($response, 'content-type');

        // Send the image as response with the appropriate content type
        header('Content-Type: ' . $type);
        echo $image;
        exit;
    }

    wp_send_json_error('Failed to fetch tile image');
}
add_action('wp_ajax_get_tile', 'inaturalist_map_get_tile');
add_action('wp_ajax_nopriv_get_tile', 'inaturalist_map_get_tile');

?>