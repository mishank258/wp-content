<?php
get_header();

// Data for the hero section
$front_page_id = (int) get_option('page_on_front');
$hero_image    = get_the_post_thumbnail_url($front_page_id, 'full');

$tours_page = get_page_by_path('tours');
$tours_url  = $tours_page ? get_permalink($tours_page) : home_url('/');

$hero_style = '';
if ($hero_image) {
    $hero_style = 'background-image: linear-gradient(rgba(0,0,0,0.45), rgba(0,0,0,0.45)), url(' . esc_url($hero_image) . ');';
}

// A custom query for the three newest travel guides
$latest_guides = new WP_Query([
    'post_type'           => 'post',
    'posts_per_page'      => 3,
    'ignore_sticky_posts' => true,
    'no_found_rows'       => true,
]);

$guides_url = get_permalink(get_option('page_for_posts'));
?>

<!-- Hero -->
<section class="kalinga-hero rounded-4 text-white d-flex align-items-center mb-5"
         style="<?php echo esc_attr($hero_style); ?>">
  <div class="p-4 p-md-5">
    <h1 class="display-4 fw-bold mb-3"><?php bloginfo('name'); ?></h1>
    <p class="lead mb-4"><?php bloginfo('description'); ?></p>
    <a href="<?php echo esc_url($tours_url); ?>" class="btn btn-light btn-lg">
      <?php esc_html_e('Explore our tours', 'kalinga'); ?>
    </a>
  </div>
</section>

<!-- Intro: the Home page's own content -->
<?php while (have_posts()) : the_post(); ?>
  <?php if (get_the_content()) : ?>
    <div class="row mb-5">
      <div class="col-lg-8 mx-auto text-center lead entry-content">
        <?php the_content(); ?>
      </div>
    </div>
  <?php endif; ?>
<?php endwhile; ?>

<!-- Latest travel guides -->
<?php if ($latest_guides->have_posts()) : ?>
  <section class="mb-5">
    <div class="d-flex justify-content-between align-items-end mb-4">
      <h2 class="mb-0"><?php esc_html_e('Latest Travel Guides', 'kalinga'); ?></h2>
      <?php if ($guides_url) : ?>
        <a href="<?php echo esc_url($guides_url); ?>"><?php esc_html_e('View all guides →', 'kalinga'); ?></a>
      <?php endif; ?>
    </div>

    <div class="row g-4">
      <?php while ($latest_guides->have_posts()) : $latest_guides->the_post(); ?>
        <?php get_template_part('template-parts/content', 'card'); ?>
      <?php endwhile; ?>
    </div>
  </section>
  <?php wp_reset_postdata(); ?>
<?php endif; ?>

<?php get_footer(); ?>