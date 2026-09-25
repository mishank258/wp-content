<?php
if (!defined('ABSPATH')) {
    exit;
}

// The allowed statuses: database value => label
function kalinga_tours_enquiry_statuses() {
    return [
        'new'       => __('New', 'kalinga-tours'),
        'contacted' => __('Contacted', 'kalinga-tours'),
        'closed'    => __('Closed', 'kalinga-tours'),
    ];
}

// Count enquiries, optionally only those with one status
function kalinga_tours_count_enquiries($status = '') {
    global $wpdb;
    $table = kalinga_tours_table_name();

    if ($status === '') {
        return (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
    }

    return (int) $wpdb->get_var(
        $wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE status = %s", $status)
    );
}

/**
 * 1. Add "Enquiries" under the Tours menu, with a badge for new ones
 */
function kalinga_tours_add_enquiries_page() {
    $new_count  = kalinga_tours_count_enquiries('new');
    $menu_title = __('Enquiries', 'kalinga-tours');

    if ($new_count > 0) {
        $menu_title .= ' <span class="awaiting-mod">' . number_format_i18n($new_count) . '</span>';
    }

    add_submenu_page(
        'edit.php?post_type=tour',               // Parent menu: Tours
        __('Tour Enquiries', 'kalinga-tours'),   // Browser tab title
        $menu_title,                             // Menu label (with badge)
        'manage_options',                        // Only administrators
        'kalinga-enquiries',                     // Page slug in the URL
        'kalinga_tours_render_enquiries_page'    // Function that draws the page
    );
}
add_action('admin_menu', 'kalinga_tours_add_enquiries_page');

/**
 * 2. Draw the Enquiries screen
 */
function kalinga_tours_render_enquiries_page() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have permission to view enquiries.', 'kalinga-tours'));
    }

    global $wpdb;
    $table    = kalinga_tours_table_name();
    $statuses = kalinga_tours_enquiry_statuses();
    $base_url = admin_url('edit.php?post_type=tour&page=kalinga-enquiries');

    // Which status to show (allow-listed)
    $current_status = isset($_GET['status']) ? sanitize_key(wp_unslash($_GET['status'])) : '';
    if (!array_key_exists($current_status, $statuses)) {
        $current_status = '';
    }

    // Pagination
    $per_page    = 20;
    $paged       = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
    $offset      = ($paged - 1) * $per_page;
    $total       = kalinga_tours_count_enquiries($current_status);
    $total_pages = max(1, (int) ceil($total / $per_page));

    // Build the query: one JOIN fetches every tour title at once
    $where  = '';
    $params = [];
    if ($current_status !== '') {
        $where    = 'WHERE e.status = %s';
        $params[] = $current_status;
    }
    $params[] = $per_page;
    $params[] = $offset;

    $sql = "SELECT e.*, p.post_title AS tour_title
            FROM {$table} e
            LEFT JOIN {$wpdb->posts} p ON p.ID = e.tour_id
            {$where}
            ORDER BY e.created_at DESC
            LIMIT %d OFFSET %d";

    $enquiries = $wpdb->get_results($wpdb->prepare($sql, $params));

    // Message after an action
    $result = isset($_GET['result']) ? sanitize_key(wp_unslash($_GET['result'])) : '';
    ?>
    <div class="wrap">
      <h1><?php esc_html_e('Tour Enquiries', 'kalinga-tours'); ?></h1>

      <?php if ($result === 'updated') : ?>
        <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Enquiry updated.', 'kalinga-tours'); ?></p></div>
      <?php elseif ($result === 'deleted') : ?>
        <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Enquiry deleted.', 'kalinga-tours'); ?></p></div>
      <?php endif; ?>

      <!-- Status filter links -->
      <ul class="subsubsub">
        <li>
          <a href="<?php echo esc_url($base_url); ?>" class="<?php echo $current_status === '' ? 'current' : ''; ?>">
            <?php esc_html_e('All', 'kalinga-tours'); ?>
            <span class="count">(<?php echo esc_html(number_format_i18n(kalinga_tours_count_enquiries())); ?>)</span>
          </a> |
        </li>
        <?php $last_key = array_key_last($statuses); ?>
        <?php foreach ($statuses as $key => $label) : ?>
          <li>
            <a href="<?php echo esc_url(add_query_arg('status', $key, $base_url)); ?>" class="<?php echo $current_status === $key ? 'current' : ''; ?>">
              <?php echo esc_html($label); ?>
              <span class="count">(<?php echo esc_html(number_format_i18n(kalinga_tours_count_enquiries($key))); ?>)</span>
            </a><?php echo $key !== $last_key ? ' |' : ''; ?>
          </li>
        <?php endforeach; ?>
      </ul>

      <table class="widefat striped" style="clear:both;">
        <thead>
          <tr>
            <th><?php esc_html_e('Received', 'kalinga-tours'); ?></th>
            <th><?php esc_html_e('Customer', 'kalinga-tours'); ?></th>
            <th><?php esc_html_e('Tour', 'kalinga-tours'); ?></th>
            <th><?php esc_html_e('Travel date', 'kalinga-tours'); ?></th>
            <th><?php esc_html_e('Travellers', 'kalinga-tours'); ?></th>
            <th><?php esc_html_e('Message', 'kalinga-tours'); ?></th>
            <th><?php esc_html_e('Status', 'kalinga-tours'); ?></th>
            <th><?php esc_html_e('Actions', 'kalinga-tours'); ?></th>
          </tr>
        </thead>
        <tbody>
          <?php if (!$enquiries) : ?>
            <tr><td colspan="8"><?php esc_html_e('No enquiries found.', 'kalinga-tours'); ?></td></tr>
          <?php endif; ?>

          <?php foreach ($enquiries as $enquiry) : ?>
            <tr>
              <td>
                <?php echo esc_html(mysql2date(get_option('date_format') . ' ' . get_option('time_format'), $enquiry->created_at)); ?>
              </td>
              <td>
                <strong><?php echo esc_html($enquiry->name); ?></strong><br>
                <a href="<?php echo esc_url('mailto:' . $enquiry->email); ?>"><?php echo esc_html($enquiry->email); ?></a>
                <?php if ($enquiry->phone) : ?>
                  <br><?php echo esc_html($enquiry->phone); ?>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($enquiry->tour_title) : ?>
                  <a href="<?php echo esc_url(get_edit_post_link($enquiry->tour_id)); ?>"><?php echo esc_html($enquiry->tour_title); ?></a>
                <?php else : ?>
                  <em><?php esc_html_e('General enquiry', 'kalinga-tours'); ?></em>
                <?php endif; ?>
              </td>
              <td>
                <?php echo $enquiry->travel_date ? esc_html(mysql2date(get_option('date_format'), $enquiry->travel_date)) : '&mdash;'; ?>
              </td>
              <td><?php echo esc_html($enquiry->travellers); ?></td>
              <td title="<?php echo esc_attr($enquiry->message); ?>">
                <?php echo esc_html(wp_trim_words($enquiry->message, 20)); ?>
              </td>
              <td>
                <strong><?php echo esc_html($statuses[$enquiry->status] ?? $enquiry->status); ?></strong>
              </td>
              <td>
                <?php foreach ($statuses as $key => $label) : ?>
                  <?php if ($key === $enquiry->status) continue; ?>
                  <a href="<?php echo esc_url(kalinga_tours_enquiry_action_url($enquiry->id, $key)); ?>">
                    <?php
                    /* translators: %s: status name */
                    printf(esc_html__('Mark %s', 'kalinga-tours'), esc_html(strtolower($label)));
                    ?>
                  </a><br>
                <?php endforeach; ?>
                <a href="<?php echo esc_url(kalinga_tours_enquiry_action_url($enquiry->id, 'delete')); ?>"
                   style="color:#b32d2e;"
                   onclick="return confirm('<?php echo esc_js(__('Delete this enquiry permanently?', 'kalinga-tours')); ?>');">
                  <?php esc_html_e('Delete', 'kalinga-tours'); ?>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <?php if ($total_pages > 1) : ?>
        <div class="tablenav"><div class="tablenav-pages">
          <?php
          echo wp_kses_post(paginate_links([
              'base'    => add_query_arg('paged', '%#%'),
              'format'  => '',
              'current' => $paged,
              'total'   => $total_pages,
          ]));
          ?>
        </div></div>
      <?php endif; ?>
    </div>
    <?php
}

// Build a secure link for an action on one enquiry
function kalinga_tours_enquiry_action_url($enquiry_id, $action) {
    $url = add_query_arg(
        [
            'action' => 'kalinga_enquiry_action',
            'id'     => (int) $enquiry_id,
            'do'     => $action,
        ],
        admin_url('admin-post.php')
    );

    return wp_nonce_url($url, 'kalinga_enquiry_action_' . (int) $enquiry_id);
}

/**
 * 3. Handle "Mark as…" and "Delete" clicks
 */
function kalinga_tours_handle_enquiry_action() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have permission to do that.', 'kalinga-tours'), 403);
    }

    $enquiry_id = isset($_GET['id']) ? absint($_GET['id']) : 0;
    check_admin_referer('kalinga_enquiry_action_' . $enquiry_id);

    $do = isset($_GET['do']) ? sanitize_key(wp_unslash($_GET['do'])) : '';

    global $wpdb;
    $table = kalinga_tours_table_name();

    if ($do === 'delete') {
        $wpdb->delete($table, ['id' => $enquiry_id], ['%d']);
        $result = 'deleted';
    } elseif (array_key_exists($do, kalinga_tours_enquiry_statuses())) {
        $wpdb->update($table, ['status' => $do], ['id' => $enquiry_id], ['%s'], ['%d']);
        $result = 'updated';
    } else {
        wp_die(esc_html__('Invalid action.', 'kalinga-tours'), 400);
    }

    $back = wp_get_referer() ?: admin_url('edit.php?post_type=tour&page=kalinga-enquiries');
    wp_safe_redirect(add_query_arg('result', $result, remove_query_arg('result', $back)));
    exit;
}
add_action('admin_post_kalinga_enquiry_action', 'kalinga_tours_handle_enquiry_action');