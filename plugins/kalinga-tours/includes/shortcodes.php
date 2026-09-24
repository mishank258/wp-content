<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Read and sanitise the filter values from a request array ($_GET or $_POST).
 */
function kalinga_tours_get_filters_from_request($source) {
    $destination = '';
    if (isset($source['destination']) && is_string($source['destination'])) {
        $destination = sanitize_key(wp_unslash($source['destination']));
    }

    $max_price = 0;
    if (isset($source['max_price']) && is_scalar($source['max_price'])) {
        $max_price = absint(wp_unslash($source['max_price']));
    }

    return [
        'destination' => $destination,
        'max_price'   => $max_price,
    ];
}

/**
 * Query tours and return the card HTML. Used by the shortcode AND the AJAX handler.
 */
function kalinga_tours_render_cards($destination = '', $max_price = 0, $limit = 12) {
    $args = [
        'post_type'      => 'tour',
        'post_status'    => 'publish',
        'posts_per_page' => $limit,
        'meta_key'       => '_kalinga_price',
        'orderby'        => 'meta_value_num',
        'order'          => 'ASC',
        'no_found_rows'  => true,
    ];

    if ($destination !== '') {
        $args['tax_query'] = [
            [
                'taxonomy' => 'destination',
                'field'    => 'slug',
                'terms'    => $destination,
            ],
        ];
    }

    if ($max_price > 0) {
        $args['meta_query'] = [
            [
                'key'     => '_kalinga_price',
                'value'   => $max_price,
                'compare' => '<=',
                'type'    => 'NUMERIC',
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
        echo '<div class="alert alert-light border">' . esc_html__('No tours match your filters. Try a different destination or price.', 'kalinga-tours') . '</div>';
    }

    wp_reset_postdata();

    return ob_get_clean();
}

/**
 * [kalinga_tours destination="spiti" limit="8" filters="yes"]
 */
function kalinga_tours_shortcode($atts) {
    $atts = shortcode_atts(
        [
            'destination' => '',
            'limit'       => 12,
            'filters'     => 'yes',
        ],
        $atts,
        'kalinga_tours'
    );

    $limit        = min(absint($atts['limit']), 50) ?: 12;
    $show_filters = ($atts['filters'] === 'yes');

    // Filters from the URL (used when JavaScript is off), falling back to the shortcode's attribute
    $request     = $show_filters ? kalinga_tours_get_filters_from_request($_GET) : ['destination' => '', 'max_price' => 0];
    $destination = $request['destination'] !== '' ? $request['destination'] : sanitize_key($atts['destination']);
    $max_price   = $request['max_price'];

    if ($show_filters) {
        wp_enqueue_script('kalinga-tours-filter');
        wp_enqueue_style('kalinga-tours');
    }

    ob_start();
    ?>
    <div class="kalinga-tours" data-limit="<?php echo esc_attr($limit); ?>">

      <?php if ($show_filters) : ?>
        <?php include KALINGA_TOURS_PATH . 'templates/tour-filters.php'; ?>
      <?php endif; ?>

      <div class="kalinga-tours-results" aria-live="polite">
        <?php
        // The card HTML is escaped inside kalinga_tours_render_cards()
        echo kalinga_tours_render_cards($destination, $max_price, $limit);
        ?>
      </div>

    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('kalinga_tours', 'kalinga_tours_shortcode');