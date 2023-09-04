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

    // Define the color ranges
    $color_ranges = array(
        'white' => array('label' => 'No reports', 'min_percentage' => 0, 'max_percentage' => 0),
        'lightgreen' => array('label' => 'Few reports', 'min_percentage' => 0, 'max_percentage' => 25),
        'green' => array('label' => 'Average reports', 'min_percentage' => 25, 'max_percentage' => 75),
        'darkgreen' => array('label' => 'Many reports', 'min_percentage' => 75, 'max_percentage' => 100),
    );

    $months = array(
        1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 
        7 => 0, 8 => 0, 9 => 0, 10 => 0, 11 => 0, 12 => 0,
    );

    $count_total = 0;

    // Generate the timeline HTML
    $timeline_html = '<div class="inat-taxonomy-timeline">';

    // Make API request to iNaturalist
    $api_url = 'https://api.inaturalist.org/v1/observations/histogram?place_id=6815&taxon_id=' . $taxon_id;
    $api_response = wp_remote_get($api_url);

    // Check if API request was successful
    if (!is_wp_error($api_response)) {
        // Parse the API response
        $api_data = json_decode(wp_remote_retrieve_body($api_response), true);
        $months = $api_data['results']['month_of_year'];

        // Sum total
        for ($month = 1; $month <= 12; $month++) {
            $count_total += $months[$month];
        }

        // Loop through each month to build html
        for ($month = 1; $month <= 12; $month++) {
            $hebrew_month_name = get_month_name_hebrew($month);

            // Calculate the percentage based on the maximum count
            $percentage = ($months[$month] / $count_total) * 100;

            // Determine the color based on the percentage
            $color = '';
            $label = '';
            foreach ($color_ranges as $range_color => $range_data) {
                if ($percentage >= $range_data['min_percentage'] && $percentage <= $range_data['max_percentage']) {
                    $color = $range_color;
                    $label = $range_data['label'];
                    break;
                }
            }

            $first_or_last = '';
            if ($month == 1) {
                $first_or_last = 'timeline-item-first';
            }
            else if ($month == 12) {
                $first_or_last = 'timeline-item-last';
            }

            // Add the timeline item to the HTML
            $timeline_html .= '<div class="timeline-item timeline-item-' . $color . ' ' . $first_or_last . '" data-month="' . $hebrew_month_name . '" data-count="' . $months[$month] . '">';
            $timeline_html .= '<div class="timeline-label">' . $hebrew_month_name . '</div>';
            $timeline_html .= '</div>';
        }
    }

    $timeline_html .= '</div>';

    return $timeline_html;
}

// Helper function to get Hebrew month name
function get_month_name_hebrew($month) {
    $hebrew_month_names = array(
        'ינואר', 'פברואר', 'מרץ', 'אפריל', 'מאי', 'יוני',
        'יולי', 'אוגוסט', 'ספטמבר', 'אוקטובר', 'נובמבר', 'דצמבר'
    );

    return isset($hebrew_month_names[$month - 1]) ? $hebrew_month_names[$month - 1] : '';
}

?>