<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<section id="enquire" class="card shadow-sm mt-5">
  <div class="card-body p-4">

    <h2 class="h4 mb-1"><?php esc_html_e('Send an enquiry', 'kalinga-tours'); ?></h2>

    <?php if ($tour_id) : ?>
      <p class="text-muted mb-4">
        <?php
        /* translators: %s: tour title */
        printf(esc_html__('About: %s', 'kalinga-tours'), esc_html(get_the_title($tour_id)));
        ?>
      </p>
    <?php else : ?>
      <p class="text-muted mb-4"><?php esc_html_e('Our team will reply within one business day.', 'kalinga-tours'); ?></p>
    <?php endif; ?>

    <?php if ($sent) : ?>
      <div class="alert alert-success" role="status">
        <?php esc_html_e('Thank you! Your enquiry has been sent. We will be in touch soon.', 'kalinga-tours'); ?>
      </div>
    <?php endif; ?>

    <?php if (isset($errors['form'])) : ?>
      <div class="alert alert-danger" role="alert"><?php echo esc_html($errors['form']); ?></div>
    <?php elseif ($errors) : ?>
      <div class="alert alert-danger" role="alert"><?php esc_html_e('Please fix the highlighted fields below.', 'kalinga-tours'); ?></div>
    <?php endif; ?>

    <form method="post" action="<?php echo esc_url(get_permalink()); ?>#enquire" class="row g-3">

      <?php wp_nonce_field('kalinga_enquiry_submit', 'kalinga_enquiry_nonce'); ?>

      <!-- Honeypot: hidden from people, filled in by bots -->
      <div style="position:absolute; left:-9999px;" aria-hidden="true">
        <label for="kalinga_website">Website</label>
        <input type="text" id="kalinga_website" name="kalinga_website" tabindex="-1" autocomplete="off">
      </div>

      <?php if ($tour_id) : ?>
        <input type="hidden" name="tour_id" value="<?php echo esc_attr($tour_id); ?>">
      <?php else : ?>
        <div class="col-12">
          <label for="enquiry_tour" class="form-label"><?php esc_html_e('Tour (optional)', 'kalinga-tours'); ?></label>
          <select id="enquiry_tour" name="tour_id" class="form-select <?php echo isset($errors['tour_id']) ? 'is-invalid' : ''; ?>">
            <option value="0"><?php esc_html_e('General enquiry', 'kalinga-tours'); ?></option>
            <?php foreach ($tours as $tour) : ?>
              <option value="<?php echo esc_attr($tour->ID); ?>" <?php selected((int) $values['tour_id'], $tour->ID); ?>>
                <?php echo esc_html($tour->post_title); ?>
              </option>
            <?php endforeach; ?>
          </select>
          <?php if (isset($errors['tour_id'])) : ?>
            <div class="invalid-feedback"><?php echo esc_html($errors['tour_id']); ?></div>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <div class="col-md-6">
        <label for="enquiry_name" class="form-label"><?php esc_html_e('Your name', 'kalinga-tours'); ?> *</label>
        <input type="text" id="enquiry_name" name="enquiry_name" maxlength="100" required
               class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>"
               value="<?php echo esc_attr($values['name']); ?>">
        <?php if (isset($errors['name'])) : ?>
          <div class="invalid-feedback"><?php echo esc_html($errors['name']); ?></div>
        <?php endif; ?>
      </div>

      <div class="col-md-6">
        <label for="enquiry_email" class="form-label"><?php esc_html_e('Email', 'kalinga-tours'); ?> *</label>
        <input type="email" id="enquiry_email" name="enquiry_email" maxlength="100" required
               class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>"
               value="<?php echo esc_attr($values['email']); ?>">
        <?php if (isset($errors['email'])) : ?>
          <div class="invalid-feedback"><?php echo esc_html($errors['email']); ?></div>
        <?php endif; ?>
      </div>

      <div class="col-md-4">
        <label for="enquiry_phone" class="form-label"><?php esc_html_e('Phone', 'kalinga-tours'); ?></label>
        <input type="tel" id="enquiry_phone" name="enquiry_phone" maxlength="30"
               class="form-control <?php echo isset($errors['phone']) ? 'is-invalid' : ''; ?>"
               value="<?php echo esc_attr($values['phone']); ?>">
        <?php if (isset($errors['phone'])) : ?>
          <div class="invalid-feedback"><?php echo esc_html($errors['phone']); ?></div>
        <?php endif; ?>
      </div>

      <div class="col-md-4">
        <label for="enquiry_date" class="form-label"><?php esc_html_e('Preferred date', 'kalinga-tours'); ?></label>
        <input type="date" id="enquiry_date" name="enquiry_date" min="<?php echo esc_attr(wp_date('Y-m-d')); ?>"
               class="form-control <?php echo isset($errors['travel_date']) ? 'is-invalid' : ''; ?>"
               value="<?php echo esc_attr($values['travel_date']); ?>">
        <?php if (isset($errors['travel_date'])) : ?>
          <div class="invalid-feedback"><?php echo esc_html($errors['travel_date']); ?></div>
        <?php endif; ?>
      </div>

      <div class="col-md-4">
        <label for="enquiry_travellers" class="form-label"><?php esc_html_e('Travellers', 'kalinga-tours'); ?> *</label>
        <input type="number" id="enquiry_travellers" name="enquiry_travellers" min="1" max="<?php echo esc_attr($max_travellers); ?>" required
               class="form-control <?php echo isset($errors['travellers']) ? 'is-invalid' : ''; ?>"
               value="<?php echo esc_attr($values['travellers']); ?>">
        <?php if (isset($errors['travellers'])) : ?>
          <div class="invalid-feedback"><?php echo esc_html($errors['travellers']); ?></div>
        <?php endif; ?>
      </div>

      <div class="col-12">
        <label for="enquiry_message" class="form-label"><?php esc_html_e('Message', 'kalinga-tours'); ?> *</label>
        <textarea id="enquiry_message" name="enquiry_message" rows="4" maxlength="2000" required
                  class="form-control <?php echo isset($errors['message']) ? 'is-invalid' : ''; ?>"><?php echo esc_textarea($values['message']); ?></textarea>
        <?php if (isset($errors['message'])) : ?>
          <div class="invalid-feedback"><?php echo esc_html($errors['message']); ?></div>
        <?php endif; ?>
      </div>

      <div class="col-12">
        <button type="submit" class="btn btn-primary"><?php esc_html_e('Send enquiry', 'kalinga-tours'); ?></button>
      </div>

    </form>
  </div>
</section>