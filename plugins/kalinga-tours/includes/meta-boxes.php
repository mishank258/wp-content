<?php
if (!defined('ABSPATH')) {
    exit;
}

// The fields: meta key in the database => form field name
function kalinga_tours_detail_fields() {
    return [
        '_kalinga_price'      => 'kalinga_price',
        '_kalinga_duration'   => 'kalinga_duration',
        '_kalinga_group_size' => 'kalinga_group_size',
    ];
}

// 1. Add the box to the tour editor screen
function kalinga_tours_add_meta_box() {
    add_meta_box(
        'kalinga_tour_details',                 // HTML id of the box
        __('Tour Details', 'kalinga-tours'),    // Title shown on the box
        'kalinga_tours_render_details_box',     // Function that outputs the box's HTML
        'tour',                                 // Only on the tour post type
        'side',                                 // Position: in the right sidebar
        'high'                                  // Priority: near the top
    );
}
add_action('add_meta_boxes', 'kalinga_tours_add_meta_box');

// 2. Output the box's HTML
function kalinga_tours_render_details_box($post) {
    wp_nonce_field('kalinga_tour_details_save', 'kalinga_tour_details_nonce');

    $price      = get_post_meta($post->ID, '_kalinga_price', true);
    $duration   = get_post_meta($post->ID, '_kalinga_duration', true);
    $group_size = get_post_meta($post->ID, '_kalinga_group_size', true);
    ?>
    <p>
      <label for="kalinga_price"><strong><?php esc_html_e('Price (AUD)', 'kalinga-tours'); ?></strong></label><br>
      <input type="number" id="kalinga_price" name="kalinga_price" min="0" step="1"
             value="<?php echo esc_attr($price); ?>" class="widefat">
    </p>
    <p>
      <label for="kalinga_duration"><strong><?php esc_html_e('Duration (days)', 'kalinga-tours'); ?></strong></label><br>
      <input type="number" id="kalinga_duration" name="kalinga_duration" min="1" step="1"
             value="<?php echo esc_attr($duration); ?>" class="widefat">
    </p>
    <p>
      <label for="kalinga_group_size"><strong><?php esc_html_e('Max group size', 'kalinga-tours'); ?></strong></label><br>
      <input type="number" id="kalinga_group_size" name="kalinga_group_size" min="1" step="1"
             value="<?php echo esc_attr($group_size); ?>" class="widefat">
    </p>
    <?php
}

// 3. Save the fields when the tour is saved
function kalinga_tours_save_details($post_id) {
    // Check the nonce: was this form really submitted from our edit screen?
    if (!isset($_POST['kalinga_tour_details_nonce'])) {
        return;
    }
    $nonce = sanitize_text_field(wp_unslash($_POST['kalinga_tour_details_nonce']));
    if (!wp_verify_nonce($nonce, 'kalinga_tour_details_save')) {
        return;
    }

    // Don't save during autosaves
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check the current user is allowed to edit this tour
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Sanitise and save each field
    foreach (kalinga_tours_detail_fields() as $meta_key => $field_name) {
        if (isset($_POST[$field_name]) && $_POST[$field_name] !== '') {
            update_post_meta($post_id, $meta_key, absint($_POST[$field_name]));
        } else {
            delete_post_meta($post_id, $meta_key);
        }
    }
}
add_action('save_post_tour', 'kalinga_tours_save_details');