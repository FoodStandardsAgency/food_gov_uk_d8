(function ($) {
  'use strict';
  Drupal.behaviors.fsaExternal = {
    attach: function (context, settings) {
      // Excluding social media icons.
      $("a[target=_blank]:not(.social-media__link)").not('.a2a_i').each(function () {
        $(this).html($(this).html() + ' <span class="visuallyhidden">'
          + Drupal.t('(Opens in a new window)')) + '</span>';
      });
    }
  };
}(jQuery));
