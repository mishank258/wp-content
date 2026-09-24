<?php
// Stop anyone loading this file directly in the browser
if (!defined('ABSPATH')) {
    exit;
}

// 1. Tell WordPress what this theme supports
function kalinga_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'gallery', 'caption', 'style', 'script']);

    register_nav_menus([
        'primary' => __('Primary Menu', 'kalinga'),
    ]);
}
add_action('after_setup_theme', 'kalinga_setup');

// 2. Load CSS and JavaScript
function kalinga_assets() {
    $version = wp_get_theme()->get('Version');

    wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css', [], '5.3.3');
    wp_enqueue_style('kalinga-style', get_stylesheet_uri(), ['bootstrap'], $version);

    wp_enqueue_script('bootstrap-bundle', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', [], '5.3.3', true);
    wp_enqueue_script('kalinga-main', get_template_directory_uri() . '/assets/js/main.js', ['jquery'], $version, true);
}
add_action('wp_enqueue_scripts', 'kalinga_assets');

// 3. Add Bootstrap's class to each <li> in the primary menu
function kalinga_menu_item_classes($classes, $item, $args) {
    if (isset($args->theme_location) && $args->theme_location === 'primary') {
        $classes[] = 'nav-item';
    }
    return $classes;
}
add_filter('nav_menu_css_class', 'kalinga_menu_item_classes', 10, 3);

// 4. Add Bootstrap's class to each <a> in the primary menu
function kalinga_menu_link_classes($atts, $item, $args) {
    if (isset($args->theme_location) && $args->theme_location === 'primary') {
        $atts['class'] = 'nav-link';
        if ($item->current) {
            $atts['class'] .= ' active';
        }
    }
    return $atts;
}
add_filter('nav_menu_link_attributes', 'kalinga_menu_link_classes', 10, 3);