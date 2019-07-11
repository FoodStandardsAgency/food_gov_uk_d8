(function ($) {
  'use strict';
  Drupal.behaviors.fsaExternal = {
    attach: function (context, settings) {
      // Excluding social media icons.
      $(":not('.social-media') a[target=_blank]:not(.social-media__link)").each(function () {
        $(this).text($(this).text() + ' ' + Drupal.t('(Opens in a new window)'));
      });
    }
  };
}(jQuery));
