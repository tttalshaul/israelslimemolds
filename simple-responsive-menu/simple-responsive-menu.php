<?php
/*
Plugin Name: Simple Responsive Menu
Description: Custom responsive top menu with hamburger for mobile
Version: 1.0
Author: Tal Shaul using ChatGPT
*/

if (!defined('ABSPATH')) exit;

// Register menu location
function srm_register_menu() {
    register_nav_menu('srm-top-menu', 'SRM Top Menu');
}
add_action('after_setup_theme', 'srm_register_menu');

// Enqueue CSS & JS
function srm_enqueue_assets() {
    if (is_admin()) return;

    wp_enqueue_style(
        'srm-style',
        plugin_dir_url(__FILE__) . 'style.css'
    );

    wp_enqueue_script(
        'srm-script',
        plugin_dir_url(__FILE__) . 'script.js',
        array(),
        false,
        true
    );
}
add_action('wp_enqueue_scripts', 'srm_enqueue_assets');

// Output menu in header
function srm_render_menu() {
    if (is_admin()) return;
    ?>
    <nav id="srm-root" class="srm-nav">
        <div class="srm-container">

            <!-- לוגו אתר (ימין) -->
            <div class="srm-right">
                <a href="<?php echo home_url(); ?>" class="srm-logo">
                    <img src="https://slimemoldsisrael.byethost7.com/wp-content/uploads/2023/03/cropped-whatsapp-image-2023-02-14-at-22.23.07.jpeg" alt="פטריריות בישראל"/>
                </a>
            </div>

            <!-- תפריט -->
            <div class="srm-center">
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'srm-top-menu',
                    'container' => false,
                    'menu_class' => 'srm-menu'
                ));
                ?>
            </div>

            <!-- שמאל: פייסבוק + המבורגר -->
            <div class="srm-left">
                <a href="https://www.facebook.com/groups/slimemoldsisrael" 
                   target="_blank" 
                   class="srm-facebook"
                   aria-label="Facebook">
                    <img width="25" height="25" style="width: 25px;" src="https://slimemoldsisrael.byethost7.com/wp-content/uploads/2023/06/f_logo_RGB-Blue_58.png" alt="קבוצת הפייסבוק של פטריריות בישראל">
                </a>

                <button class="srm-toggle" aria-label="Toggle Menu">
                    ☰
                </button>
            </div>

        </div>
    </nav>
    <?php
}
if (!is_admin()) {
    add_action('wp_body_open', 'srm_render_menu');
}
?>