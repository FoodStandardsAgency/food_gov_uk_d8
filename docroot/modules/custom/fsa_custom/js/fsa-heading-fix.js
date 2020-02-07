(function ($) {
  'use strict';
  Drupal.behaviors.fsaHeadingFix = {
    attach: function (context, settings) {
      // Excluding social media icons.
      $('div[role=heading]').each(function () {
        var $head = jQuery(this);
        var level = $head.closest('.embedded-entity').prevAll('h3')[0].nodeName.substr(1);
        $head.attr('aria-level', parseInt(level) + 1);
      });
    }
  };
}(jQuery));


