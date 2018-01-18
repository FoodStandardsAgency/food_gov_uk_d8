(function ($) {
  'use strict';
  Drupal.behaviors.fsaHistoryBack = {
    attach: function (context, settings) {
      $('.page-print-trigger').once().click(function(e) {
        e.preventDefault();
        window.print();
        return false;
      });
    }
  };

}(jQuery));
