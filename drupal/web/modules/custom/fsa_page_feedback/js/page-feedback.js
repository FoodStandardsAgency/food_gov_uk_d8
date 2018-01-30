(function ($) {
  'use strict';
  Drupal.behaviors.fsaPageFeedback = {
    attach: function (context, settings) {
      $('#edit-is-useful-yes').once().click(function(e) {
        var form = $(this).closest('form');
        form.find(':submit').trigger('click');
        form.fadeOut('fast');
      });
    }
  };

}(jQuery));
