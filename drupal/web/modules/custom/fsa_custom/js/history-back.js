(function ($) {
  'use strict';
  Drupal.behaviors.fsaHistoryBack = {
    attach: function (context, settings) {
      $('a.history-back').once().click(function(e) {
        e.preventDefault();
        parent.history.back();
        return false;
      });
    }
  };

}(jQuery));
