<?php
if (!defined('ABSPATH')) {
    exit;
}

// [kalinga_tours destination="spiti" limit="8"]
function kalinga_tours_shortcode($atts) {
    $atts = shortcode_atts(
        [
            'destination' => '',
            'limit'       => 12,
        ],
        $atts,
        'kalinga_tours'
    );

    $args = [
        'post_type'      => 'tour',
        'post_status'    => 'publish',
        'posts_per_page' => absint($atts['limit']),
        'meta_key'       => '_kalinga_price',
        'orderby'        => 'meta_value_num',
        'order'          => 'ASC',
        'no_found_rows'  => true,
    ];

    // Only filter by destination if one was given
    $destination = sanitize_key($atts['destination']);
    if ($destination !== '') {
        $args['tax_query'] = [
            [
                'taxonomy' => 'destination',
                'field'    => 'slug',
                'terms'    => $destination,
            ],
        ];
    }

    $tours = new WP_Query($args);

    ob_start();

    if ($tours->have_posts()) {
        echo '<div class="row g-4">';
        while ($tours->have_posts()) {
            $tours->the_post();
            include KALINGA_TOURS_PATH . 'templates/tour-card.php';
        }
        echo '</div>';
    } else {
        echo '<p>' . esc_html__('No tours found.', 'kalinga-tours') . '</p>';
    }

    wp_reset_postdata();

    return ob_get_clean();
}
add_shortcode('kalinga_tours', 'kalinga_tours_shortcode');