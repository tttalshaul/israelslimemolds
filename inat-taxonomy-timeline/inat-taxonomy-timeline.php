<?php
/*
Plugin Name: iNaturalist Taxon Timeline
Description: Display timeline of months in which a specific taxon is reported on iNaturalist.
Version: 1.0
Author: Tal Shaul using ChatGPT
Author URI: http://slimemoldsisrael.byethost7.com
Plugin URI: http://slimemoldsisrael.byethost7.com
*/

function inat_taxon_timeline_enqueue_scripts() {
    // Enqueue custom CSS file
    wp_enqueue_style('inat-taxonomy-timeline-css', plugin_dir_url(__FILE__) . 'style/inat-taxonomy-timeline.css', array(), '1.0');

    wp_enqueue_script('inat-taxonomy-timeline-js', plugin_dir_url(__FILE__) . 'js/inat-taxonomy-timeline.js', array('jquery'), '1.0', true);

    // Enqueue Chart JS library
    wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js', array(), 'latest', true);

    // // Pass data to JavaScript using wp_localize_script
    // wp_localize_script('inat-taxonomy-timeline-js', 'ajax_object', array(
    //     'ajax_url' => admin_url('admin-ajax.php'), // WordPress AJAX endpoint
    // ));
}
add_action('wp_enqueue_scripts', 'inat_taxon_timeline_enqueue_scripts');

// Register the shortcode
add_shortcode('inat_taxon_timeline', 'inat_taxon_timeline_shortcode');

// Shortcode callback function
function inat_taxon_timeline_shortcode($atts) {
    // Get the taxon ID from shortcode parameters
    $taxon_id = isset($atts['taxon_id']) ? $atts['taxon_id'] : '';

    // Check if taxon ID is provided
    if (empty($taxon_id)) {
        return '<p>No taxon ID provided.</p>';
    }

    // Generate the timeline HTML
    $timeline_html = '<div class="inat-taxonomy-timeline" data-taxon-id="' . esc_attr($taxon_id) . '"></div>';
    //$timeline_html += '<div class="inat-taxonomy-timeline-by-area"><canvas id="inat-taxonomy-timeline-canvas" width="800" height="400"></canvas></div>';

    return $timeline_html;
}

// Register the shortcode
add_shortcode('inat_taxon_area_by_month_timeline', 'inat_taxon_area_by_month_shortcode');

// Shortcode callback function
function inat_taxon_area_by_month_shortcode($atts) {
    // Generate the timeline HTML
    $timeline_html = '<div class="inat-taxonomy-timeline-by-area"><canvas id="inat-taxonomy-timeline-canvas" width="800" height="400"></canvas></div>';

    return $timeline_html;
}

// Define the AJAX callback function to retrieve iNaturalist data
// function get_inat_data() {
//     // Get the taxon ID from the AJAX request
//     $taxon_id = $_GET['taxon_id'];

//     // Make the API request to iNaturalist using your existing PHP code
//     $api_url = 'https://api.inaturalist.org/v1/observations/histogram?place_id=6815&taxon_id=' . $taxon_id;
//     $api_response = wp_remote_get($api_url);

//     // Check if API request was successful
//     if (!is_wp_error($api_response)) {
//         // Parse the API response and process the data
//         $api_data = json_decode(wp_remote_retrieve_body($api_response), true);

//         // Extract the data you need from $api_data and format it as desired
//         $data = $api_data['results']['month_of_year']; // Replace with the data you want to return

//         wp_send_json_success($data);
//     } else {
//         // Handle errors if the API request fails
//         wp_send_json_error('Error fetching data from iNaturalist');
//     }

//     wp_die(); // Always include wp_die() at the end to terminate the AJAX request
// }

// // Hook the AJAX action to WordPress
// add_action('wp_ajax_get_inat_data', 'get_inat_data');
// add_action('wp_ajax_nopriv_get_inat_data', 'get_inat_data'); // For non-logged-in users

?>