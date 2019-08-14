(function ($) {
  'use strict';
  Drupal.behaviors.fsaHeadings = {
    attach: function (context, settings) {
      $('h1, h2, h3, h4').each(function(index) {
        $(this).append('{' + $(this).prop('nodeName') + '}') ;
      } )
    }
  };
}(jQuery));
