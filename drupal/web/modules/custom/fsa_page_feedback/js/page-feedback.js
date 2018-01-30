(function ($) {
  'use strict';
  Drupal.behaviors.fsaPageFeedback = {
    attach: function (context, settings) {

      var toggler = '.page-feedback fieldset.is_useful',
          wrapper = '.page-feedback fieldset.feedback_wrapper',
          submit = '.page-feedback form #edit-actions-submit',
          radio_yes = '#edit-is-useful-yes',
          radio_no = '#edit-is-useful-no',
          open_text = Drupal.t('Is there anything wrong with this page?'),
          close_text = Drupal.t('Close');

      // Supporting buttons to open/close the feedback form.
      $(toggler).once().append('<button type="button" value="open" class="toggler open-feedback">'+open_text+'</button>');
      $(wrapper).once().prepend('<button type="button" value="close" class="toggler close-feedback">'+close_text+'</button>');

      // Hide the feedback form by default.
      $(wrapper).hide();
      $(submit).hide();

      // Open the feedback-container and show submit button.
      $('.toggler.open-feedback, #edit-is-useful-no').click(function(e) {
        $(radio_no).prop('checked', true);
        $(toggler).hide();
        $(submit).show();
        $(wrapper).slideDown();
      });

      // Close the container/hide submit
      $('.toggler.close-feedback').click(function(e) {
        $(radio_no).prop('checked', false);
        $(submit).hide();
        $(wrapper).slideUp();
        $(toggler).fadeIn();
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
