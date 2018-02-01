/**
 * @file
 * FSA Ratings search javascript additions.
 */
(function ($) {
  'use strict';
  Drupal.behaviors.FsaRatingsSearch = {
    attach: function (context, settings) {

      // Form ID's to uncheck the other option-set on selection.
      var forms = {
        "edit-fhrs-rating-value": "edit-fhis-rating-value",
        "edit-fhis-rating-value": "edit-fhrs-rating-value"
      };
      $.each( forms, function( check, uncheck ) {
        $('#'+check, context).change(function() {
          $('#'+uncheck+' input').prop('checked', false);
        });
      });

    }
  };
} (jQuery));
