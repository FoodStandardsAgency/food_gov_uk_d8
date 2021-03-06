(function ($) {
  'use strict';
  Drupal.behaviors.fsaPageFeedback = {
    attach: function (context, settings) {

      var radios_fieldset = '.page-feedback fieldset.is_useful',
          wrapper_fieldset = '.page-feedback fieldset.feedback_wrapper',
          submit = '.page-feedback form input[type="submit"]',
          radio_yes = '#edit-is-useful-yes',
          radio_no = '#edit-is-useful-no',
          open_text = Drupal.t('Is there anything wrong with this page?'),
          close_text = Drupal.t('Close'),
          close_aria_text = Drupal.t('the feedback form');
        var form = $('.footer-top-wrapper form');

      // Supporting buttons to open/close the feedback form.
      $(wrapper_fieldset).once().prepend('<button type="button" value="close" class="toggler close-feedback">'+close_text+'<span class="visually-hidden">'+close_aria_text+'</span></button>');

      if ($('.page-feedback .message-list').length === 0) {
        // Hide the feedback form by default as long as no visible errors.
        $(wrapper_fieldset).hide();
      }
      else {
        // On errors the form should already be visible, hide the radios fieldset.
        $(radios_fieldset).hide();
      }

      // Open the feedback-container and show submit button.
      $('.page-feedback--no, #open-feedback').click(function(e) {
        e.preventDefault();
        $('.is_useful').val('No')
        $(radios_fieldset).hide();
        $(submit).show();
        $(wrapper_fieldset).slideDown();
        $('.page-feedback--p').fadeOut('fast');      
      });

      // Close the container.
      $('.toggler.close-feedback').click(function(e) {
        $(wrapper_fieldset).slideUp();
        $(submit).hide();
      });

      $('.page-feedback--yes').once().click(function(e) {
        e.preventDefault();
        $('.is_useful').val('Yes')
        form.find(':submit').trigger('click');
        form.fadeOut('fast');
        $('.page-feedback--p').fadeOut('fast');      
      });

      $('#edit-is-useful--wrapper').hide();
      $('#edit-is-useful').remove();
      form.append('<input class="is_useful" name="is_useful" value="Yes" type="hidden">');    
      $(submit).hide();

      setTimeout(function() {
        jQuery('#a2apage_full a').each(function() { 
          if (["Email", "Facebook", "WhatsApp", "Twitter", "LinkedIn", "Gmail", "Facebook Messenger", "Copy Link", "Pinterest", "Tumblr"].indexOf(jQuery(this).text().trim()) === -1) {
            jQuery(this).remove();
          }
        });
      }, 500);
    }
  };

}(jQuery));
