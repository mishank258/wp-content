<?php get_header(); ?>

<?php while (have_posts()) : the_post(); ?>
  <div class="row">
    <article <?php post_class('col-lg-8 mx-auto'); ?>>

      <h1 class="mb-2"><?php the_title(); ?></h1>

      <p class="text-muted small mb-4">
        <?php echo esc_html(get_the_date()); ?>
        &middot; <?php the_category(', '); ?>
      </p>

      <?php if (has_post_thumbnail()) : ?>
        <?php the_post_thumbnail('large', ['class' => 'img-fluid rounded mb-4']); ?>
      <?php endif; ?>

      <div class="entry-content">
        <?php the_content(); ?>
      </div>

      <?php the_tags('<p class="mt-5 small">' . esc_html__('Tags:', 'kalinga') . ' ', ', ', '</p>'); ?>

    </article>
  </div>
<?php endwhile; ?>

<?php get_footer(); ?>