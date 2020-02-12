(function ($) {
  'use strict';

  Drupal.behaviors.linkitContentAccessibility = {
    attach: function (context, settings) {

      // Set focus on first search result of new page loaded with AJAX.
      $('a[data-doctype]').each(function () {
        var doctype = jQuery(this).data('doctype');
        if (doctype && doctype.length) {
          jQuery(this).append('<span class="visuallyhidden">as ' + doctype + '</span>');
        }
      });

    }
  };

}(jQuery));
