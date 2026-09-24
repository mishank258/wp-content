jQuery(function ($) {
  // Set up every tours block on the page (there could be more than one)
  $('.kalinga-tours').each(function () {
    var $wrapper = $(this);
    var $form = $wrapper.find('.kalinga-tours-filter');
    var $results = $wrapper.find('.kalinga-tours-results');
    var currentRequest = null;

    function showError() {
      var $alert = $('<div class="alert alert-danger"></div>').text(KalingaTours.errorMessage);
      $results.html($alert);
    }

    function loadTours() {
      // Cancel a request that's still running, so old results can't overwrite new ones
      if (currentRequest) {
        currentRequest.abort();
      }

      $wrapper.addClass('is-loading');
      $results.attr('aria-busy', 'true');

      currentRequest = $.ajax({
        url: KalingaTours.ajaxUrl,
        method: 'POST',
        dataType: 'json',
        data: {
          action: 'kalinga_tours_filter',
          nonce: KalingaTours.nonce,
          destination: $form.find('[name="destination"]').val(),
          max_price: $form.find('[name="max_price"]').val(),
          limit: $wrapper.data('limit')
        }
      })
        .done(function (response) {
          if (response.success) {
            $results.html(response.data.html);
          } else {
            showError();
          }
        })
        .fail(function (xhr, status) {
          if (status !== 'abort') {
            showError();
          }
        })
        .always(function (data, status) {
          if (status !== 'abort') {
            $wrapper.removeClass('is-loading');
            $results.attr('aria-busy', 'false');
            currentRequest = null;
          }
        });
    }

    // Filter as soon as a dropdown changes
    $form.on('change', 'select', loadTours);

    // Stop the normal page reload when the button is clicked, and use AJAX instead
    $form.on('submit', function (event) {
      event.preventDefault();
      loadTours();
    });
  });
});