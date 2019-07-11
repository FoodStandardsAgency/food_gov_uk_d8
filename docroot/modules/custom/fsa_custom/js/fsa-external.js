(function ($) {
  'use strict';
  Drupal.behaviors.fsaExternal = {
    attach: function (context, settings) {
      // Excluding social media icons.
      $("a[target=_blank]:not(.social-media__link)").each(function () {
        $(this).html($(this).text() + ' <span class="visuallyhidden">'
          + Drupal.t('(Opens in a new window)')) + '</span>';
      });
    }
  };
}(jQuery));
