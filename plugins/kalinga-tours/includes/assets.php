<?php
if (!defined('ABSPATH')) {
    exit;
}

function kalinga_tours_register_assets() {
    wp_register_style(
        'kalinga-tours',
        KALINGA_TOURS_URL . 'assets/css/tours.css',
        [],
        KALINGA_TOURS_VERSION
    );

    wp_register_script(
        'kalinga-tours-filter',
        KALINGA_TOURS_URL . 'assets/js/tours-filter.js',
        ['jquery'],
        KALINGA_TOURS_VERSION,
        true
    );

    wp_localize_script('kalinga-tours-filter', 'KalingaTours', [
        'ajaxUrl'      => admin_url('admin-ajax.php'),
        'nonce'        => wp_create_nonce('kalinga_tours_filter'),
        'errorMessage' => __('Sorry, something went wrong. Please try again.', 'kalinga-tours'),
    ]);
}
add_action('wp_enqueue_scripts', 'kalinga_tours_register_assets');