(function ($) {
  'use strict';
  var waitForEl = function(selector, callback) {
    if (jQuery(selector).length) {
      callback();
    } else {
      setTimeout(function() {
        waitForEl(selector, callback);
      }, 100);
    }
  };

  Drupal.behaviors.fsaAddtoanyAccesibility = {
    attach: function (context, settings) {
      // Changing focus to the addtoany popup after clicking on "More..."
      // We can't seem to use a live event here.
      waitForEl('#a2apage_show_more_less', function () {
        $('#a2apage_show_more_less').click(function (e) {
          setTimeout(function () {
            $('#a2apage_full .a2a_i:first-child').focus();
          }, 100);
        });
      });
    }
  };
}(jQuery));
