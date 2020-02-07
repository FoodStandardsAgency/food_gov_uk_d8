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

      $('#block-languageswitcher, #block-languageswitcher-2').attr('aria-label', 'Language selection');
      $('nav[role="navigation"], article[role="article"]').removeAttr('role');
    }
  };
}(jQuery));


