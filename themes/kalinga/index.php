<?php get_header(); ?>

<?php if (have_posts()) : ?>

  <div class="row g-4">
    <?php while (have_posts()) : the_post(); ?>
      <div class="col-md-6 col-lg-4">
        <article <?php post_class('card h-100 shadow-sm'); ?>>

          <?php if (has_post_thumbnail()) : ?>
            <a href="<?php the_permalink(); ?>">
              <?php the_post_thumbnail('medium_large', ['class' => 'card-img-top']); ?>
            </a>
          <?php endif; ?>

          <div class="card-body d-flex flex-column">
            <h2 class="h5 card-title">
              <a href="<?php the_permalink(); ?>" class="text-decoration-none"><?php the_title(); ?></a>
            </h2>
            <div class="card-text text-muted small mb-3"><?php the_excerpt(); ?></div>
            <a href="<?php the_permalink(); ?>" class="btn btn-outline-primary btn-sm mt-auto align-self-start">
              <?php esc_html_e('Read more', 'kalinga'); ?>
            </a>
          </div>

        </article>
      </div>
    <?php endwhile; ?>
  </div>

  <div class="mt-5">
    <?php the_posts_pagination(); ?>
  </div>

<?php else : ?>
  <p><?php esc_html_e('No content found.', 'kalinga'); ?></p>
<?php endif; ?>

<?php get_footer(); ?>