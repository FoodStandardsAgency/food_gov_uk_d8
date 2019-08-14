(function ($) {
  'use strict';
  Drupal.behaviors.fsaSVGs = {
    attach: function (context, settings) {
      $("svg").each(function () {
        $(this).attr('focusable', 'false');
      });
    }
  };
}(jQuery));
