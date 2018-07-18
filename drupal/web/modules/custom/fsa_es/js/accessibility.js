(function ($) {
  'use strict';

  Drupal.behaviors.fsaElasticsearchAccessibility = {
    attach: function (context, settings) {

      // Set focus on first search result of new page loaded with AJAX.
      $(document, context).once('search-ajax-load-accessibility').ajaxSuccess(function(event, xhr, settings) {
        if (settings.url.startsWith('/views')) {
          $('.listing a', context).first().focus();
        }
      });

    }
  };

}(jQuery));
