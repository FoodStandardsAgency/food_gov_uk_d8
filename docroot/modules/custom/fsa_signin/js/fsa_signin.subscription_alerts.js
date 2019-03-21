/**
 * @file
 * A JavaScript file for the fsa_signin module.
 */

(function ($) {
  'use strict';

  Drupal.behaviors.fsaSignIn = {
    // Drupal's core JS will execute behaviors' attach functions when the DOM is ready.
    attach: function (context, settings) {

      // Form ids to the array to implement "select all" option for checkbox lists.
      // Script expects FAPI to have the "all" option key named "all".
      var forms = [
        "edit-subscribed-news",
        "edit-subscribed-allergy-alerts",
        "edit-news-tids-for-registration",
        "edit-cons-tids-for-registration",
        "edit-alert-tids-for-registration"
      ];
      $.each( forms, function( index, value ) {
        $('#'+value+'-all', context).change(function() {
          var $checkboxes = $('#'+value+'--wrapper input.form-checkbox', context);

          if ($('#'+value+'-all', context)[0].checked) {
            $checkboxes.prop('checked', true);
          }
          else {
            $checkboxes.prop('checked', false);
          }
        });
      });
    }

  };

} (jQuery));
