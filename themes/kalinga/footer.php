</main>

<footer class="bg-dark text-light py-4 mt-5">
  <div class="container d-flex flex-column flex-md-row justify-content-between gap-2">
    <p class="mb-0"><?php bloginfo('name'); ?> &middot; <?php bloginfo('description'); ?></p>
    <p class="mb-0">&copy; <?php echo esc_html(wp_date('Y')); ?> <?php bloginfo('name'); ?></p>
  </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>