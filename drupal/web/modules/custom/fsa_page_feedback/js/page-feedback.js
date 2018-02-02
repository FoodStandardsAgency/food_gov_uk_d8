(function ($) {
  'use strict';
  Drupal.behaviors.fsaPageFeedback = {
    attach: function (context, settings) {

      var radios_fieldset = '.page-feedback fieldset.is_useful',
          wrapper_fieldset = '.page-feedback fieldset.feedback_wrapper',
          submit = '.page-feedback form #edit-actions-submit',
          radio_yes = '#edit-is-useful-yes',
          radio_no = '#edit-is-useful-no',
          open_text = Drupal.t('Is there anything wrong with this page?'),
          close_text = Drupal.t('Close');

      // Supporting buttons to open/close the feedback form.
      $(radios_fieldset).once().append('<button id="open-feedback" type="button" value="open" class="toggler open-feedback">'+open_text+'</button>');
      $(wrapper_fieldset).once().prepend('<button type="button" value="close" class="toggler close-feedback">'+close_text+'</button>');

      if ($('.page-feedback .message-list').length === 0) {
        // Hide the feedback form by default as long as no visible errors.
        $(wrapper_fieldset).hide();
        $(submit).hide();
      }
      else {
        // On errors the form should already be visible, hide the radios fieldset.
        $(radios_fieldset).hide();
      }

      // Open the feedback-container and show submit button.
      $('.toggler.open-feedback, #edit-is-useful-no').click(function(e) {
        $(radio_no).prop('checked', true);
        $(radios_fieldset).hide();
        $(submit).show();
        $(wrapper_fieldset).slideDown();
      });

      // Close the container/hide submit
      $('.toggler.close-feedback').click(function(e) {
        $(radio_no).prop('checked', false);
        $(submit).hide();
        $(wrapper_fieldset).slideUp();
        $(radios_fieldset).fadeIn();
      });

      // Auto-trigger a submit on yes.
      $('.form-autosubmit-on-yes '+radio_yes).once().click(function(e) {
        var form = $(this).closest('form');
        form.find(':submit').trigger('click');
        form.fadeOut('fast');
      });
    }
  };

}(jQuery));
