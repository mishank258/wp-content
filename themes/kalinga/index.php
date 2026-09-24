<?php get_header(); ?>

<?php if (have_posts()) : ?>

  <div class="row g-4">
    <?php while (have_posts()) : the_post(); ?>
        <?php get_template_part('template-parts/content', 'card'); ?>
    <?php endwhile; ?>
  </div>

  <div class="mt-5">
    <?php the_posts_pagination(); ?>
  </div>

<?php else : ?>
  <p><?php esc_html_e('No content found.', 'kalinga'); ?></p>
<?php endif; ?>

<?php get_footer(); ?>