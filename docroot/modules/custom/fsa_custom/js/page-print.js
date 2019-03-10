(function ($) {
  'use strict';
  Drupal.behaviors.fsaPagePrint = {
    attach: function (context, settings) {
      $('button.page-print-trigger').once().click(function(e) {
        e.preventDefault();
        window.print();
        return false;
      });

      $( document ).ready(function() {
        if (window.location.href.indexOf('?print=1') !== -1 && $('body').hasClass('theme--multipage_guide')) {
          // On multi page guide pages, trigger the print function if its requested in the url.
          window.print();
        }
      });
    }
  };

}(jQuery));
