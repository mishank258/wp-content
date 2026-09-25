<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Keeps the form's errors and submitted values for the current request,
 * so the form can be shown again with the visitor's answers still filled in.
 */
function kalinga_tours_enquiry_state($new_state = null) {
    static $state = ['errors' => [], 'values' => []];

    if ($new_state !== null) {
        $state = $new_state;
    }

    return $state;
}

/**
 * Handle a submitted enquiry, before the page is displayed.
 */
function kalinga_tours_handle_enquiry() {
    if (!isset($_POST['kalinga_enquiry_nonce'])) {
        return; // Not an enquiry submission
    }

    // Where to send the visitor after a successful submission
    $redirect_url = get_permalink(get_queried_object_id()) ?: home_url('/');

    // 1. Security: check the nonce
    $nonce = sanitize_text_field(wp_unslash($_POST['kalinga_enquiry_nonce']));
    if (!wp_verify_nonce($nonce, 'kalinga_enquiry_submit')) {
        kalinga_tours_enquiry_state([
            'errors' => ['form' => __('Your session expired. Please refresh the page and try again.', 'kalinga-tours')],
            'values' => [],
        ]);
        return;
    }

    // 2. Spam: the honeypot field is hidden from people, so only bots fill it in
    if (!empty($_POST['kalinga_website'])) {
        wp_safe_redirect(add_query_arg('enquiry', 'sent', $redirect_url) . '#enquire');
        exit;
    }
        // Rate limit: at most 3 enquiries per 10 minutes from the same IP address
    $ip       = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';
    $rate_key = 'kalinga_enq_' . md5($ip);
    $attempts = (int) get_transient($rate_key);

    if ($attempts >= 3) {
        kalinga_tours_enquiry_state([
            'errors' => ['form' => __('You have sent several enquiries recently. Please wait a few minutes and try again.', 'kalinga-tours')],
            'values' => [],
        ]);
        return;
    }

    // 3. Read and sanitise every field
    $raw = function ($key) {
        return (isset($_POST[$key]) && is_string($_POST[$key])) ? wp_unslash($_POST[$key]) : '';
    };

    $values = [
        'tour_id'     => absint($raw('tour_id')),
        'name'        => sanitize_text_field($raw('enquiry_name')),
        'email'       => sanitize_email($raw('enquiry_email')),
        'phone'       => preg_replace('/[^0-9+()\-\s]/', '', sanitize_text_field($raw('enquiry_phone'))),
        'travel_date' => sanitize_text_field($raw('enquiry_date')),
        'travellers'  => absint($raw('enquiry_travellers')),
        'message'     => sanitize_textarea_field($raw('enquiry_message')),
    ];

    // 4. Validate
    $errors = kalinga_tours_validate_enquiry($values);
    if ($errors) {
        kalinga_tours_enquiry_state(['errors' => $errors, 'values' => $values]);
        return;
    }

    // 5. Save to the database FIRST
    $enquiry_id = kalinga_tours_save_enquiry($values);
    if (!$enquiry_id) {
        kalinga_tours_enquiry_state([
            'errors' => ['form' => __('Sorry, we could not save your enquiry. Please try again.', 'kalinga-tours')],
            'values' => $values,
        ]);
        return;
    }
        // Count this submission towards the rate limit
    set_transient($rate_key, $attempts + 1, 10 * MINUTE_IN_SECONDS);


    // 6. Then notify the admin by email
    kalinga_tours_send_enquiry_email($values, $enquiry_id);

    // 7. Redirect, so refreshing the page can't submit the form twice
    wp_safe_redirect(add_query_arg('enquiry', 'sent', $redirect_url) . '#enquire');
    exit;
}
add_action('template_redirect', 'kalinga_tours_handle_enquiry');

/**
 * Check every field and return an array of error messages (empty = valid).
 */
function kalinga_tours_validate_enquiry($values) {
    $errors = [];

    if ($values['name'] === '') {
        $errors['name'] = __('Please enter your name.', 'kalinga-tours');
    } elseif (mb_strlen($values['name']) > 100) {
        $errors['name'] = __('Your name must be 100 characters or fewer.', 'kalinga-tours');
    }

    if (!is_email($values['email'])) {
        $errors['email'] = __('Please enter a valid email address.', 'kalinga-tours');
    }

    if (mb_strlen($values['phone']) > 30) {
        $errors['phone'] = __('Please enter a shorter phone number.', 'kalinga-tours');
    }

    if ($values['travel_date'] !== '') {
        $date = DateTime::createFromFormat('Y-m-d', $values['travel_date']);
        if (!$date || $date->format('Y-m-d') !== $values['travel_date']) {
            $errors['travel_date'] = __('Please choose a valid date.', 'kalinga-tours');
        } elseif ($values['travel_date'] < wp_date('Y-m-d')) {
            $errors['travel_date'] = __('Please choose a date in the future.', 'kalinga-tours');
        }
    }

    // The tour must exist, and the group can't be bigger than the tour allows
    $max_travellers = 30;
    if ($values['tour_id']) {
        if (get_post_type($values['tour_id']) !== 'tour' || get_post_status($values['tour_id']) !== 'publish') {
            $errors['tour_id'] = __('Please choose a valid tour.', 'kalinga-tours');
        } else {
            $details = kalinga_tours_get_details($values['tour_id']);
            if ($details['group_size']) {
                $max_travellers = $details['group_size'];
            }
        }
    }

    if ($values['travellers'] < 1 || $values['travellers'] > $max_travellers) {
        $errors['travellers'] = sprintf(
            /* translators: %d: maximum number of travellers */
            __('Please enter between 1 and %d travellers.', 'kalinga-tours'),
            $max_travellers
        );
    }

    if ($values['message'] === '') {
        $errors['message'] = __('Please enter a message.', 'kalinga-tours');
    } elseif (mb_strlen($values['message']) > 2000) {
        $errors['message'] = __('Your message must be 2,000 characters or fewer.', 'kalinga-tours');
    }

    return $errors;
}

/**
 * Insert the enquiry into the custom table. Returns the new ID, or 0 on failure.
 */
function kalinga_tours_save_enquiry($values) {
    global $wpdb;

    $inserted = $wpdb->insert(
        kalinga_tours_table_name(),
        [
            'tour_id'     => $values['tour_id'],
            'name'        => $values['name'],
            'email'       => $values['email'],
            'phone'       => $values['phone'],
            'travel_date' => $values['travel_date'] !== '' ? $values['travel_date'] : null,
            'travellers'  => $values['travellers'],
            'message'     => $values['message'],
            'status'      => 'new',
            'created_at'  => current_time('mysql'),
        ],
        ['%d', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s']
    );

    return $inserted ? (int) $wpdb->insert_id : 0;
}

/**
 * Email the site admin about a new enquiry.
 */
function kalinga_tours_send_enquiry_email($values, $enquiry_id) {
    $tour_title = $values['tour_id'] ? get_the_title($values['tour_id']) : __('General enquiry', 'kalinga-tours');

    $subject = sprintf(
        /* translators: 1: enquiry ID, 2: tour title */
        __('New enquiry #%1$d: %2$s', 'kalinga-tours'),
        $enquiry_id,
        $tour_title
    );

    $body = implode("\n", [
        'Tour: ' . $tour_title,
        'Name: ' . $values['name'],
        'Email: ' . $values['email'],
        'Phone: ' . ($values['phone'] ?: '-'),
        'Travel date: ' . ($values['travel_date'] ?: '-'),
        'Travellers: ' . $values['travellers'],
        '',
        $values['message'],
    ]);

    $headers = ['Reply-To: ' . $values['name'] . ' <' . $values['email'] . '>'];

    $sent = wp_mail(get_option('admin_email'), $subject, $body, $headers);

    if (!$sent) {
        error_log('Kalinga Tours: email notification failed for enquiry #' . $enquiry_id);
    }
}

/**
 * Build the form's HTML. Used by the shortcode and by single-tour.php.
 */
function kalinga_tours_render_enquiry_form($tour_id = 0) {
    $tour_id = absint($tour_id);
    $state   = kalinga_tours_enquiry_state();
    $errors  = $state['errors'];
    $values  = wp_parse_args($state['values'], [
        'tour_id'     => $tour_id,
        'name'        => '',
        'email'       => '',
        'phone'       => '',
        'travel_date' => '',
        'travellers'  => 2,
        'message'     => '',
    ]);

    $sent = isset($_GET['enquiry']) && $_GET['enquiry'] === 'sent';

    // On the Contact page (no tour given), let the visitor choose a tour
    $tours = $tour_id ? [] : get_posts([
        'post_type'   => 'tour',
        'numberposts' => -1,
        'orderby'     => 'title',
        'order'       => 'ASC',
    ]);

    $max_travellers = 30;
    if ($tour_id) {
        $details = kalinga_tours_get_details($tour_id);
        if ($details['group_size']) {
            $max_travellers = $details['group_size'];
        }
    }

    ob_start();
    include KALINGA_TOURS_PATH . 'templates/enquiry-form.php';
    return ob_get_clean();
}

// [kalinga_enquiry_form] or [kalinga_enquiry_form tour_id="25"]
function kalinga_tours_enquiry_shortcode($atts) {
    $atts = shortcode_atts(['tour_id' => 0], $atts, 'kalinga_enquiry_form');

    $tour_id = absint($atts['tour_id']);
    if (!$tour_id && is_singular('tour')) {
        $tour_id = get_the_ID();
    }

    return kalinga_tours_render_enquiry_form($tour_id);
}
add_shortcode('kalinga_enquiry_form', 'kalinga_tours_enquiry_shortcode');