<?php
/*
Plugin Name: Tag Categories
Description: Creates a custom taxonomy to group tags.
Version: 1.0
Author: Tal Shaul using ChatGPT
Author URI: http://slimemoldsisrael.byethost7.com
Plugin URI: http://slimemoldsisrael.byethost7.com
*/

function enqueue_tag_categories_styles() {
    wp_enqueue_style('tag-categories-styles', plugins_url( 'tag-categories.css', __FILE__ ) );
}
add_action('wp_enqueue_scripts', 'enqueue_tag_categories_styles');


// Register a custom taxonomy for grouping tags
function register_tag_category_taxonomy() {
    $labels = array(
        'name' => 'תגי פטריריות',
        'singular_name' => 'תג',
        'search_items' => 'חפש תג',
        'all_items' => 'כל התגים',
        'parent_item' => 'תג אב',
        'parent_item_colon' => 'תג אב:',
        'edit_item' => 'לערוך תג',
        'update_item' => 'לעדכן תג',
        'add_new_item' => 'להוסיף תג חדש',
        'new_item_name' => 'שם התג החדש',
        'menu_name' => 'תגי פטריריות',
    );

    $args = array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'tag-category'),
    );

    register_taxonomy('tag_category', 'post', $args);
}
add_action('init', 'register_tag_category_taxonomy');

// Shortcode to display tag categories grouped by their parent terms
function display_tag_categories_by_parent_shortcode($atts) {
    $atts = shortcode_atts(array(), $atts);

    if (is_single()) {
        global $post;

        // Get terms from the tag_category taxonomy associated with the current post
        $tag_categories = get_the_terms($post->ID, 'tag_category');

        if ($tag_categories && !is_wp_error($tag_categories)) {
            // Organize terms by their parent terms
            $term_groups = array();
            $term_group_parents = array();
            foreach ($tag_categories as $tag_category) {
                if ($tag_category->parent === 0) {
                    $parent_term = $tag_category;
                } else {
                    $parent_term = get_term($tag_category->parent, 'tag_category');
                    if (is_numeric($tag_category->name)) {
                        $parent_term = get_term($parent_term->parent, 'tag_category');
                    }
                }

                $group_parent_term = get_term($parent_term->parent, 'tag_category');
                if (!is_wp_error($group_parent_term) && !isset($term_group_parents[$group_parent_term->name])) {
                    $term_group_parents[$group_parent_term->name] = array(
                        'parent' => array(),
                        'children' => array(),
                    );
                }

                if (!is_wp_error($group_parent_term) && !is_wp_error($parent_term) && !isset($term_groups[$parent_term->name])) {
                    $term_groups[$parent_term->name] = array(
                        'parent' => $parent_term,
                        'children' => array(),
                    );
                    $term_group_parents[$group_parent_term->name]['children'][] = &$term_groups[$parent_term->name];
                    $term_group_parents[$group_parent_term->name]['parent'][] = $parent_term->name;
                }
                $term_groups[$parent_term->name]['children'][] = $tag_category;
            }

            // Output the organized term groups
            $output = '<div class="tag-categories">';
            foreach ($term_group_parents as $term_group) {
                array_multisort($term_group['parent'], $term_group['children']);
                ksort($term_group);
                foreach ($term_group['children'] as $tag) {
                    $output .= '<div class="tag-category">';
                    $output .= '<div class="tag-category-parent">' . $tag['parent']->name . '</div>';
                
                    foreach ($tag['children'] as $child_index => $child_term) {
                        $arrow = "";
                        if (is_numeric($child_term->name)) {
                            $child_term = get_term($child_term->parent, 'tag_category');
                            if ($child_index !== array_key_last($tag['children'])) {
                                $arrow = "<div class='tag-category-arrow'>◂</div>";
                            }
                        }
                        $output .= '<div class="tag-category-child">' . $child_term->name . '</div>' . $arrow;
                    }
                    
                    $output .= '</div>';
                }
            }
            $output .= '</div>';
            return $output;
        }
    }

    return ''; // Return an empty string if no tag_category terms or not a single post
}
add_shortcode('tag_categories_by_parent', 'display_tag_categories_by_parent_shortcode');

// Remove the default "Tags" column from the admin posts view
function remove_tags_column($columns) {
    unset($columns['tags']);
    unset($columns['comments']);
    return $columns;
}
add_filter('manage_posts_columns', 'remove_tags_column');

// Remove the content from the removed "Tags" column
function remove_tags_column_content($column_name, $post_id) {
    if ($column_name === 'tags' or $column_name === 'comments') {
        return; // Return nothing to remove the content from the column
    }
}
add_action('manage_posts_custom_column', 'remove_tags_column_content', 10, 2);

?>
