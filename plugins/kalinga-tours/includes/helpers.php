<?php
if (!defined('ABSPATH')) {
    exit;
}

// Get a tour's details as an array of whole numbers
function kalinga_tours_get_details($post_id) {
    return [
        'price'      => (int) get_post_meta($post_id, '_kalinga_price', true),
        'duration'   => (int) get_post_meta($post_id, '_kalinga_duration', true),
        'group_size' => (int) get_post_meta($post_id, '_kalinga_group_size', true),
    ];
}

// Format a price like "$1,299 AUD"
function kalinga_tours_format_price($amount) {
    return '$' . number_format_i18n($amount) . ' AUD';
}

// Get the names of a tour's destinations, e.g. ['Spiti Valley']
function kalinga_tours_get_destination_names($post_id) {
    $terms = get_the_terms($post_id, 'destination');

    if (!$terms || is_wp_error($terms)) {
        return [];
    }

    return wp_list_pluck($terms, 'name');
}