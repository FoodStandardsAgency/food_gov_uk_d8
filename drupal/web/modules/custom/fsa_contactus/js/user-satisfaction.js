(function ($) {
  'use strict';
  Drupal.behaviors.FsaUserSatisfactionForm = {
    attach: function (context, settings) {

      var block = '#block-user-satisfaction-form',
          container = '#block-user-satisfaction-form .webform',
          has_messages = '#block-user-satisfaction-form .message-list',
          is_submitted = '#block-user-satisfaction-form .webform-confirmation';

      // Supporting button to close the satisfaction form.
      $(container).once().prepend('<button type="button" value="close" class="toggler close-satisfaction-form">'+Drupal.t('Close')+'</button>');

      // Hide the form as long as there are no errors and user did not already
      // submit feedback.
      if ($(has_messages).length === 0 && $(is_submitted).length === 0) {
        $(container).hide();
      }

      // We don't need the title/toggler once form is succesfully submitted.
      if ($(is_submitted).length > 0) {
        $('.open-satisfaction-form').remove();
        $('.close-satisfaction-form').remove();
      }

      // Open the form and add class to style the title.
      $('#block-user-satisfaction-form .open-satisfaction-form').click(function(e) {
        $(container).slideDown();
        $(block).addClass('form-visible');
      });

      // Close the form and remove "title" styling class.
      $('.toggler.close-satisfaction-form').click(function(e) {
        $(block).removeClass('form-visible');
        $(container).slideUp();
      });

      // Actual form disappears on submit, as a quick usability win add the
      // approximate height of the form to prevent page jumping.
      $(is_submitted).height(650);

    }
  };

}(jQuery));
