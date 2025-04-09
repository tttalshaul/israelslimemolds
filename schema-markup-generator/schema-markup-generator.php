<?php
/*
Plugin Name: Schema Markup Generator
Description: Generates Schema markup for rich results using post images and excerpt.
Version: 1.0
Author: Tal Shaul using ChatGPT
*/

// Helper function to remove Nikud (vowel points) from a Hebrew string
function markup_remove_nikud( $string ) {
    // Remove the Nikud (vowel points) from the string using regex
    // The Unicode range for Hebrew Nikud is U+05B0 to U+05BD
    return preg_replace('/[\x{05B0}-\x{05BD}]+/u', '', $string);
}

function decode_unicode_escape_sequences($input) {
    return json_decode('"' . $input . '"', true, 512, JSON_UNESCAPED_UNICODE);
}

function get_video_url() {
    global $post;

    $content = apply_filters('the_content', $post->post_content);

    if (strpos($content, '</video>')) {
        if (preg_match_all('/<video[^>]+src=["\']([^"\']+)["\']/', $content, $matches_video)) {
            return $matches_video;
        }
    }
    return false;
}

function generate_schema_markup() {
    if (is_single()) {
        global $post;
        global $wp;

        $title = markup_remove_nikud(get_the_title($post->ID));
        $description = markup_remove_nikud(get_the_excerpt($post->ID));

        $schema_data = array(
            "@context" => "http://schema.org",
            "@type" => "BlogPosting",
            "headline" => $title,
            "author" => [array(
                "@type" => "Person",
                "name" => "Tal Shaul",
                "url" => "https://www.facebook.com/profile.php?id=1423542954",
            )],
            "url" => add_query_arg( $wp->query_vars, home_url() ),
            "image" => array(),
            "description" => $description,
        );

        // Get all images attached to the post
        $images = get_attached_media('image', $post->ID);
        foreach ($images as $image) {
            $schema_data['image'][] = wp_get_attachment_url($image->ID);
        }

        // Output the Schema markup
        $json_data = json_encode($schema_data, JSON_UNESCAPED_UNICODE);
        if ($json_data === false) {
            echo 'JSON encoding error: ' . json_last_error_msg();
        } else {
            echo '<script type="application/ld+json">' . $json_data . '</script>';
        }

        $video_url = get_video_url();
        if ($video_url) {
            if (has_post_thumbnail($post->ID)) {
                $thumb_url = get_the_post_thumbnail_url($post->ID, 'large'); // or 'medium', 'full'
            }
            else {
                $thumb_url = 'https://slimemoldsisrael.byethost7.com/wp-content/uploads/2023/03/whatsapp-image-2023-02-14-at-22.23.07-1.jpeg';
            }

            $upload_date = get_the_modified_date('Y-m-d\TH:i:sP', $post->ID);

            $schema_data = array(
                "@context" => "http://schema.org",
                "@type" => "VideoObject",
                "name" => $title,
                "description" => $description,
                "uploadDate" => $upload_date,
                "thumbnailUrl" => $thumb_url,
                "contentUrl" => $video_url[1][0],
                "embedUrl" => add_query_arg( $wp->query_vars, home_url() ),
            );

            $json_data = json_encode($schema_data, JSON_UNESCAPED_UNICODE);

            if ($json_data === false) {
                echo 'JSON encoding error: ' . json_last_error_msg();
            } else {
                echo '<script type="application/ld+json">' . $json_data . '</script>';
            }
        }
    }
}

add_action('wp_footer', 'generate_schema_markup');

function add_social_meta_tags() {
    if (!is_front_page() && !is_home() && (is_single() || is_page())) {
        global $post;
        setup_postdata($post);

        $og_title = get_the_title();
        $og_description = get_the_excerpt();
        $og_image = get_the_post_thumbnail_url($post, 'large');

        echo '<meta property="og:title" content="' . esc_attr($og_title) . '">';
        echo '<meta property="og:description" content="' . esc_attr($og_description) . '">';
        echo '<meta property="og:image" content="' . esc_url($og_image) . '">';
        echo '<meta property="og:url" content="' . esc_url(get_permalink()) . '">';
    }
}

add_action('wp_head', 'add_social_meta_tags');

?>
