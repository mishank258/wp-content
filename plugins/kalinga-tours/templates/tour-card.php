<?php
if (!defined('ABSPATH')) {
    exit;
}

$details      = kalinga_tours_get_details(get_the_ID());
$destinations = kalinga_tours_get_destination_names(get_the_ID());
?>
<div class="col-md-6 col-lg-3">
  <article class="card h-100 shadow-sm">

    <?php if (has_post_thumbnail()) : ?>
      <a href="<?php the_permalink(); ?>">
        <?php the_post_thumbnail('medium_large', ['class' => 'card-img-top']); ?>
      </a>
    <?php endif; ?>

    <div class="card-body d-flex flex-column">

      <?php if ($destinations) : ?>
        <span class="badge text-bg-secondary align-self-start mb-2">
          <?php echo esc_html(implode(', ', $destinations)); ?>
        </span>
      <?php endif; ?>

      <h3 class="h5 card-title">
        <a href="<?php the_permalink(); ?>" class="text-decoration-none"><?php the_title(); ?></a>
      </h3>

      <?php if ($details['duration']) : ?>
        <p class="text-muted small mb-2">
          <?php
          printf(
              esc_html(_n('%d day', '%d days', $details['duration'], 'kalinga-tours')),
              $details['duration']
          );
          ?>
        </p>
      <?php endif; ?>

      <p class="fw-bold mb-3">
        <?php
        echo $details['price']
            ? esc_html(kalinga_tours_format_price($details['price']))
            : esc_html__('Price on request', 'kalinga-tours');
        ?>
      </p>

      <a href="<?php the_permalink(); ?>" class="btn btn-primary btn-sm mt-auto align-self-start">
        <?php esc_html_e('View tour', 'kalinga-tours'); ?>
      </a>
    </div>

  </article>
</div>