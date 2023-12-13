<?php
/*
Plugin Name: Schema Markup Generator
Description: Generates Schema markup for rich results using post images and excerpt.
Version: 1.0
Author: Tal Shaul using ChatGPT
*/

// Helper function to remove Nikud (vowel points) from a Hebrew string
function remove_nikud( $string ) {
    // Remove the Nikud (vowel points) from the string using regex
    // The Unicode range for Hebrew Nikud is U+05B0 to U+05BD
    return preg_replace('/[^\p{L} ]+/', '', $string);
}

function generate_schema_markup() {
    if (is_single()) {
        global $post;

        $schema_data = array(
            "@context" => "http://schema.org",
            "@type" => "BlogPosting",
            "headline" => remove_nikud(get_the_title($post->ID)),
            "image" => array(),
            "description" => remove_nikud(get_the_excerpt($post->ID))
        );

        // Get all images attached to the post
        $images = get_attached_media('image', $post->ID);
        foreach ($images as $image) {
            $schema_data['image'][] = wp_get_attachment_url($image->ID);
        }

        // Output the Schema markup
        echo '<script type="application/ld+json">' . json_encode($schema_data) . '</script>';
    }
}

add_action('wp_footer', 'generate_schema_markup');

function add_social_meta_tags() {
    if (is_single() || is_page()) {
        global $post;
        setup_postdata($post);

        $og_title = get_the_title();
        $og_description = get_the_excerpt();
        $og_image = get_the_post_thumbnail_url($post, 'large');

        echo '<meta property="og:title" content="' . esc_attr($og_title) . '">';
        echo '<meta property="og:description" content="' . esc_attr($og_description) . '">';
        echo '<meta property="og:image" content="' . esc_url($og_image) . '">';
        echo '<meta property="og:url" content="' . esc_url(get_permalink()) . '">';
    } else {
        // For the home page or other non-single views
        echo '<meta property="og:title" content="' . esc_attr(get_bloginfo('name')) . '">';
        echo '<meta property="og:description" content="' . esc_attr(get_bloginfo('description')) . '">';
        echo '<meta property="og:image" content="' . esc_url(get_theme_mod('custom_logo')) . '">';
        echo '<meta property="og:url" content="' . esc_url(home_url('/')) . '">';
    }
}

add_action('wp_head', 'add_social_meta_tags');

?>