(function ($) {
  'use strict';
  
  Drupal.behaviors.fsaWebform = {
    attach: function (context, settings) {
      // removing aria-describedby attribute on confirm email field
      $('#edit-reg-products-emails-confirm-mail-2').removeAttr("aria-describedby");

      //renaming the anchor for the email when there's an error to the proper id
      if ($('.error-summary a[href="#edit-reg-products-emails-confirm"]').length != 0) {
        $('.error-summary a[href="#edit-reg-products-emails-confirm"]').attr('href', '#edit-reg-products-emails-confirm--wrapper');
      }
    }
  };
}(jQuery));