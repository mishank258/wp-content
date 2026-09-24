<?php get_header(); ?>

<?php while (have_posts()) : the_post(); ?>
  <article <?php post_class(); ?>>
    <header class="mb-4">
      <h1 class="mb-3"><?php the_title(); ?></h1>
      <div class="lead text-muted"><?php the_excerpt(); ?></div>
    </header>

    <div class="entry-content">
      <?php the_content(); ?>
    </div>
  </article>
<?php endwhile; ?>

<?php get_footer(); ?>