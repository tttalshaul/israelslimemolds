<?php

/*
Plugin Name: Post Taxonomy Editor
Plugin URI: http://slimemoldsisrael.byethost7.com
Description: Allows to specify hierarchical taxonomies to posts
Version: 1.0
Author: Tal Shaul using ChatGPT
Author URI: http://slimemoldsisrael.byethost7.com
*/

// Register "taxonomy" custom attribute
function custom_taxonomy() {
    $labels = array(
        'name'              => _x( 'שיוך טקסונומי', 'taxonomy general name' ),
        'singular_name'     => _x( 'שיוך טקסונומי', 'taxonomy singular name' ),
        'search_items'      => __( 'חיפוש שיוך' ),
        'all_items'         => __( 'כל השיוכים' ),
        'parent_item'       => __( 'אב טקסונומי' ),
        'parent_item_colon' => __( 'אב טקסונומי:' ),
        'edit_item'         => __( 'לערוך שיוך' ),
        'update_item'       => __( 'לעדכן שיוך' ),
        'add_new_item'      => __( 'להוסיף שיוך חדש' ),
        'new_item_name'     => __( 'שם טקסונומי חדש' ),
        'menu_name'         => __( 'שיוך טקסונומי' ),
    );

    $args = array(
        'hierarchical'      => true, // This makes it hierarchical
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'taxonomy' ),
    );

    register_taxonomy( 'taxonomy', array( 'post' ), $args );
}
add_action( 'init', 'custom_taxonomy', 0 );

// Helper function to remove Nikud (vowel points) from a Hebrew string
function remove_nikud( $string ) {
    // Remove the Nikud (vowel points) from the string using regex
    // The Unicode range for Hebrew Nikud is U+05B0 to U+05BD
    return preg_replace('/[^\p{L} ]+/', '', $string);
}

// Get the post id by exact title
function get_post_id_by_exact_title( $title, $term_id ) {
    $args = array(
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'tax_query' => array(
            array(
                'taxonomy' => 'taxonomy',
                'field'    => 'term_id',
                'terms'    => $term_id,
            ),
        ),
    );

    $title_without_nikud = remove_nikud($title);

    // echo '<br/>title: ' . $title;
    // var_dump($title);
    // echo '<br/>title_without_nikud: ' . $title_without_nikud;
    // var_dump($title_without_nikud);
    // echo '<br/>';

    $query = new WP_Query( $args );
    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            $post_title = remove_nikud(get_the_title());
            // echo '<br/>post_title: ' . $post_title;
            // var_dump($post_title);
            // echo '<br/>';
            if ( $post_title == $title_without_nikud ) {
                $result_post_id = get_the_ID();
                // echo '<br/>result_post_id: ';
                // var_dump($result_post_id);
                wp_reset_postdata();
                return $result_post_id; // Return the id of the first matching post
            }
        }
        wp_reset_postdata();
    }

    return false; // Return false if no matching post found
}

// Shortcode to display taxonomy hierarchy on species posts
function custom_species_taxonomy_hierarchy_shortcode() {
    ob_start();
    
    if ( is_single() && has_term( '', 'taxonomy' ) ) {
        $post_id = get_the_ID();
        $taxonomy_terms = get_the_terms( $post_id, 'taxonomy' );
        $has_parent = true;
        if ( $taxonomy_terms && ! is_wp_error( $taxonomy_terms ) ) {
            $taxonomy_hierarchy = array();
            $current_taxonomy = $taxonomy_terms[0];
            while ( $current_taxonomy && ! is_wp_error( $current_taxonomy ) && $has_parent !== 0 ) {
                // Find the post associated with the current term
                $current_post_id = get_post_id_by_exact_title($current_taxonomy->name, $current_taxonomy->term_id);
                $hierarchy_spaces = str_repeat("&nbsp;", count($taxonomy_hierarchy) * 2);

                if ( $current_post_id ) {
                    // Get the link of the post associated with the term
                    $post_permalink = get_permalink( $current_post_id );
                    $post_category_name = get_the_category( $current_post_id )[0]->description;
                    if ($post_id !== $current_post_id) {
                        $taxonomy_hierarchy[] = $hierarchy_spaces . '<a href="' . $post_permalink . '">' . $post_category_name . ': ' . $current_taxonomy->name . '</a>';
                    }
                }
                else {
                    // No posts found for the term, so just append the term name without a link
                    $taxonomy_hierarchy[] = $hierarchy_spaces . $current_taxonomy->name;
                }
                $current_taxonomy = get_term( $current_taxonomy->parent, 'taxonomy' );
                $has_parent = $current_taxonomy->parent;
            }
            echo '<p class="taxonomy_hierarchy">היררכיה טקסונומית: <br/>' . implode( '<br/>', $taxonomy_hierarchy ) . '</p>';
        }
    }

    return ob_get_clean();
}
add_shortcode( 'species_taxonomy_hierarchy', 'custom_species_taxonomy_hierarchy_shortcode' );

// Shortcode to display photo gallery on taxonomy posts
function custom_taxonomy_photo_gallery_shortcode() {
    ob_start();
    
    if ( is_single() && has_term( '', 'taxonomy' ) ) {
        $post_id = get_the_ID();
        $taxonomy_terms = get_the_terms( $post_id, 'taxonomy' );
        if ( $taxonomy_terms && ! is_wp_error( $taxonomy_terms ) ) {
            echo '<div class="tax-container">';
            $taxonomy_term_id = $taxonomy_terms[0]->term_id;
            $child_terms = get_terms( 'taxonomy', array( 'parent' => $taxonomy_term_id ) );

            if ( ! is_wp_error( $child_terms ) ) {
                if ( $child_terms) {
                    foreach ( $child_terms as $child_term ) {
                        $current_post_id = get_post_id_by_exact_title($child_term->name, $child_term->term_id);
                        $post_permalink = get_permalink($current_post_id);

                        echo '<div class="tax-item"><a href="' . $post_permalink . '">';
                        echo '<div class="tax-title">' . $child_term->name . '</div>';

                        // Get the post thumbnail
                        if (has_post_thumbnail($current_post_id)) {
                            $thumbnail = get_the_post_thumbnail($current_post_id, 'thumbnail');
                            echo '<div class="tax-thumbnail">' . $thumbnail . '</div>';
                        }
                        echo '</a></div>';

                        // $taxonomy_query_args = array(
                        //     'post_type'      => 'post',
                        //     'posts_per_page' => 1,
                        //     'tax_query'      => array(
                        //         array(
                        //             'taxonomy' => 'taxonomy',
                        //             'field'    => 'term_id',
                        //             'terms'    => $child_term->term_id,
                        //         ),
                        //     ),
                        // );

                        // $taxonomy_query = new WP_Query( $taxonomy_query_args );
                        // if ( $taxonomy_query->have_posts() ) {
                        //     while ( $taxonomy_query->have_posts() ) : $taxonomy_query->the_post();
                        //         echo '<div class="tax-item"><a href="' . get_permalink() . '">';
                        //         echo '<div class="tax-title">' . $child_term->name . '</div>';
                        //         if ( has_post_thumbnail() ) {
                        //             echo '<div class="tax-thumbnail">';
                        //             the_post_thumbnail( 'thumbnail' );
                        //             echo '</div>';
                        //         }
                        //         echo '</a></div>';
                        //     endwhile;
                        // }
                    }
                    // wp_reset_postdata();
                }
                else {
                    $taxonomy_query_args = array(
                        'post_type'      => 'post',
                        'posts_per_page' => -1,
                        'post__not_in'   => array( get_the_ID() ),
                        'tax_query'      => array(
                            array(
                                'taxonomy' => 'taxonomy',
                                'field'    => 'term_id',
                                'terms'    => $taxonomy_term_id,
                            ),
                        ),
                    );

                    $taxonomy_query = new WP_Query( $taxonomy_query_args );
                    if ( $taxonomy_query->have_posts() ) {
                        while ( $taxonomy_query->have_posts() ) : $taxonomy_query->the_post();
                            echo '<div class="tax-item">';
                            echo '<a href="' . get_permalink() . '"><div class="tax-title">' . get_the_title() . '</div>';
                            if ( has_post_thumbnail() ) {
                                echo '<div class="tax-thumbnail">';
                                the_post_thumbnail( 'thumbnail' );
                                echo '</div>';
                            }
                            echo '</a></div>';
                        endwhile;
                        wp_reset_postdata();
                    }
                }
            }
            echo '</div>';
        }
    }

    return ob_get_clean();
}
add_shortcode( 'taxonomy_photo_gallery', 'custom_taxonomy_photo_gallery_shortcode' );

function enqueue_taxonomy_gallery_styles() {
    wp_enqueue_style( 'taxonomy-photo-gallery-styles', plugins_url( 'style.css', __FILE__ ) );
}
add_action( 'wp_enqueue_scripts', 'enqueue_taxonomy_gallery_styles' );

?>