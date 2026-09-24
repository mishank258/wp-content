<?php get_header(); ?>

<?php while (have_posts()) : the_post(); ?>
  <?php
  $details = function_exists('kalinga_tours_get_details')
      ? kalinga_tours_get_details(get_the_ID())
      : null;
  ?>

  <div class="row g-5">

    <!-- Main content -->
    <article <?php post_class('col-lg-8'); ?>>
      <?php the_terms(get_the_ID(), 'destination', '<p class="text-uppercase small fw-semibold text-muted mb-2">', ', ', '</p>'); ?>

      <h1 class="mb-4"><?php the_title(); ?></h1>

      <?php if (has_post_thumbnail()) : ?>
        <?php the_post_thumbnail('large', ['class' => 'img-fluid rounded mb-4']); ?>
      <?php endif; ?>

       <div class="entry-content">
        <?php the_content(); ?>
      </div>

      <?php
      if (function_exists('kalinga_tours_render_enquiry_form')) {
          // The form HTML is escaped inside the plugin's template
          echo kalinga_tours_render_enquiry_form(get_the_ID());
      }
      ?>
    </article>

    <!-- Booking sidebar -->
    <aside class="col-lg-4">
      <?php if ($details) : ?>
        <div class="card shadow-sm">
          <div class="card-body p-4">

            <p class="display-6 fw-bold mb-0">
              <?php
              echo $details['price']
                  ? esc_html(kalinga_tours_format_price($details['price']))
                  : esc_html__('Price on request', 'kalinga');
              ?>
            </p>
            <p class="text-muted small mb-4"><?php esc_html_e('per person', 'kalinga'); ?></p>

            <ul class="list-unstyled mb-4">
              <?php if ($details['duration']) : ?>
                <li class="mb-2">
                  <strong><?php esc_html_e('Duration:', 'kalinga'); ?></strong>
                  <?php
                  printf(
                      esc_html(_n('%d day', '%d days', $details['duration'], 'kalinga')),
                      $details['duration']
                  );
                  ?>
                </li>
              <?php endif; ?>

              <?php if ($details['group_size']) : ?>
                <li>
                  <strong><?php esc_html_e('Max group size:', 'kalinga'); ?></strong>
                  <?php
                  printf(
                      esc_html(_n('%d traveller', '%d travellers', $details['group_size'], 'kalinga')),
                      $details['group_size']
                  );
                  ?>
                </li>
              <?php endif; ?>
            </ul>

            <a href="#enquire" class="btn btn-primary w-100">
              <?php esc_html_e('Enquire about this tour', 'kalinga'); ?>
            </a>

          </div>
        </div>
      <?php endif; ?>
    </aside>

  </div>
<?php endwhile; ?>

<?php get_footer(); ?>