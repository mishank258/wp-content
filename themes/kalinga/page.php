<?php get_header(); ?>

<?php while (have_posts()) : the_post(); ?>
  <div class="row">
    <article <?php post_class('col-lg-8 mx-auto'); ?>>
      <h1 class="mb-4"><?php the_title(); ?></h1>

      <div class="entry-content">
        <?php the_content(); ?>
      </div>
    </article>
  </div>
<?php endwhile; ?>

<?php get_footer(); ?>