(function ($) {
  'use strict';
  Drupal.behaviors.recaptchaAccessibility = {
    attach: function (context, settings) {
      $("#g-recaptcha-response").attr("aria-hidden", "true");
      $("#g-recaptcha-response").attr("aria-label", "do not use");
      $("#g-recaptcha-response").attr("aria-readonly", "true");
    }
  };

}(jQuery));
