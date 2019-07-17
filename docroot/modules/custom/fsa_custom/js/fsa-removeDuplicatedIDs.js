(function ($) {
  'use strict';
  Drupal.behaviors.fsaRemoveDuplicatedIDs = {
    attach: function (context, settings) {
      // Removing duplicated IDs.
      $('#block-search-keyword, #edit-keywords, #edit-fax, #search-keyword').each(function () {
        $(this).removeAttr('id');
      });
    }
  };
}(jQuery));
