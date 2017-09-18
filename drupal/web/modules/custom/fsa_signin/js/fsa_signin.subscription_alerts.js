/**
 * @file
 * A JavaScript file for the fsa_signin module.
 */

(function ($) {
  'use strict';

  Drupal.behaviors.fsaSignIn = {
    // Drupal's core JS will execute behaviors' attach functions when the DOM is ready.
    attach: function (context, settings) {
      $('#edit-alert-tids-for-registration-all', context).change(function() {
        var checkAll = $('#edit-alert-tids-for-registration-all', context)[0].checked;
        var $checkboxes = $('#edit-alert-tids-for-registration--wrapper input.form-checkbox', context);
        if (checkAll) {
          $checkboxes.prop('checked', true);
        }
        else {
          $checkboxes.prop('checked', false);
        }
      });
    }

  };

} (jQuery));
