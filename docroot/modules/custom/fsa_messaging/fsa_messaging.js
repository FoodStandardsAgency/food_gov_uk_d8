(function ($) {
  'use strict';

  Drupal.behaviors.fsa_messaging = {
    attach: function(context, settings) {
      $.ajax({
        url: '/' + settings.langPrefix + 'ajax/fsa-messaging',
        success: function($result) {
          $('#fsa-messaging-block-wrapper').replaceWith($result);
        }
      });
    }
  };

}(jQuery));
