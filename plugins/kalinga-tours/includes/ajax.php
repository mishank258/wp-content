<?php
if (!defined('ABSPATH')) {
    exit;
}

function kalinga_tours_ajax_filter() {
    // Stop the request if the nonce is missing or invalid
    check_ajax_referer('kalinga_tours_filter', 'nonce');

    $filters = kalinga_tours_get_filters_from_request($_POST);

    $limit = 12;
    if (isset($_POST['limit']) && is_scalar($_POST['limit'])) {
        $limit = min(absint(wp_unslash($_POST['limit'])), 50) ?: 12;
    }

    $html = kalinga_tours_render_cards($filters['destination'], $filters['max_price'], $limit);

    wp_send_json_success(['html' => $html]);
}
add_action('wp_ajax_kalinga_tours_filter', 'kalinga_tours_ajax_filter');
add_action('wp_ajax_nopriv_kalinga_tours_filter', 'kalinga_tours_ajax_filter');