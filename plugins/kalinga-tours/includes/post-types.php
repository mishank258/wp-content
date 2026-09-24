<?php
if (!defined('ABSPATH')) {
    exit;
}

// Register the "Tour" post type
function kalinga_tours_register_post_type() {
    $labels = [
        'name'          => __('Tours', 'kalinga-tours'),
        'singular_name' => __('Tour', 'kalinga-tours'),
        'add_new_item'  => __('Add New Tour', 'kalinga-tours'),
        'edit_item'     => __('Edit Tour', 'kalinga-tours'),
        'all_items'     => __('All Tours', 'kalinga-tours'),
        'search_items'  => __('Search Tours', 'kalinga-tours'),
        'not_found'     => __('No tours found.', 'kalinga-tours'),
    ];

    register_post_type('tour', [
        'labels'        => $labels,
        'public'        => true,
        'has_archive'   => false,
        'rewrite'       => ['slug' => 'tour'],
        'menu_icon'     => 'dashicons-palmtree',
        'menu_position' => 5,
        'supports'      => ['title', 'editor', 'thumbnail', 'excerpt'],
        'show_in_rest'  => true,
    ]);
}
add_action('init', 'kalinga_tours_register_post_type');

// Register the "Destination" taxonomy for tours and travel guides
function kalinga_tours_register_taxonomy() {
    $labels = [
        'name'          => __('Destinations', 'kalinga-tours'),
        'singular_name' => __('Destination', 'kalinga-tours'),
        'add_new_item'  => __('Add New Destination', 'kalinga-tours'),
        'edit_item'     => __('Edit Destination', 'kalinga-tours'),
        'all_items'     => __('All Destinations', 'kalinga-tours'),
        'search_items'  => __('Search Destinations', 'kalinga-tours'),
    ];

    register_taxonomy('destination', ['tour', 'post'], [
        'labels'            => $labels,
        'hierarchical'      => true,
        'public'            => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'rewrite'           => ['slug' => 'destination'],
    ]);
}
add_action('init', 'kalinga_tours_register_taxonomy');