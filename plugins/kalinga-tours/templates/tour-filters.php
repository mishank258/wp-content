<?php
if (!defined('ABSPATH')) {
    exit;
}

$destination_terms = get_terms([
    'taxonomy'   => 'destination',
    'hide_empty' => true,
]);

$price_options = [
    0    => __('Any price', 'kalinga-tours'),
    700  => __('Up to $700', 'kalinga-tours'),
    1000 => __('Up to $1,000', 'kalinga-tours'),
    1500 => __('Up to $1,500', 'kalinga-tours'),
    2000 => __('Up to $2,000', 'kalinga-tours'),
];
?>
<form class="kalinga-tours-filter row g-3 align-items-end mb-4" method="get">

  <div class="col-sm-6 col-lg-4">
    <label for="kalinga-destination" class="form-label small fw-semibold">
      <?php esc_html_e('Destination', 'kalinga-tours'); ?>
    </label>
    <select id="kalinga-destination" name="destination" class="form-select">
      <option value=""><?php esc_html_e('All destinations', 'kalinga-tours'); ?></option>
      <?php if (!is_wp_error($destination_terms)) : ?>
        <?php foreach ($destination_terms as $term) : ?>
          <option value="<?php echo esc_attr($term->slug); ?>" <?php selected($destination, $term->slug); ?>>
            <?php echo esc_html($term->name); ?>
          </option>
        <?php endforeach; ?>
      <?php endif; ?>
    </select>
  </div>

  <div class="col-sm-6 col-lg-4">
    <label for="kalinga-max-price" class="form-label small fw-semibold">
      <?php esc_html_e('Budget per person', 'kalinga-tours'); ?>
    </label>
    <select id="kalinga-max-price" name="max_price" class="form-select">
      <?php foreach ($price_options as $value => $label) : ?>
        <option value="<?php echo esc_attr($value); ?>" <?php selected($max_price, $value); ?>>
          <?php echo esc_html($label); ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="col-lg-4 d-flex align-items-center gap-3">
    <button type="submit" class="btn btn-primary"><?php esc_html_e('Filter tours', 'kalinga-tours'); ?></button>
    <span class="kalinga-tours-spinner spinner-border spinner-border-sm text-primary" role="status" aria-hidden="true"></span>
  </div>

</form>