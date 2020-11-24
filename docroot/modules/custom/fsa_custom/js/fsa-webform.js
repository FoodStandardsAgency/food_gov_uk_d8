(function ($) {
  'use strict';
  
  Drupal.behaviors.fsaWebform = {
    attach: function (context, settings) {
      // removing aria-describedby attribute on confirm email field
      $('#edit-reg-products-emails-confirm-mail-2').removeAttr("aria-describedby");
    }
  };
}(jQuery));