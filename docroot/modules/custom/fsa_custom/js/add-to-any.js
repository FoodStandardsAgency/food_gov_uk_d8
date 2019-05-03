(function ($) {
  'use strict';
  Drupal.behaviors.fsaAddToAny = {
    attach: function (context, settings) {

      $('a.addtoany_share', context).html(Drupal.t('Share')).removeClass('ext');

    }
  };


}(jQuery));
