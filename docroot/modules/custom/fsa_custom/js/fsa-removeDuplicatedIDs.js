(function ($) {
  'use strict';
  Drupal.behaviors.fsaRemoveDuplicatedIDs = {
    attach: function (context, settings) {
      // Removing duplicated IDs.
      $('#block-search-keyword, #edit-keywords, #edit-fax, #search-keyword, #edit-actions, #edit-fax--2').each(function () {
        $(this).removeAttr('id');
      });
    }
  };
  Drupal.behaviors.fsaNotifyDuplicateIDs = {
    attach: function (context, settings) {
      if (window.location.hostname !== 'www.food.gov.uk') {
        $('[id]').each(function(){
          var ids = $('[id="' + this.id + '"]');
          if (ids.length > 1 && ids[0] == this) {
            console.warn('Multiple IDs #' + this.id);
          }
        });
      }
    }
  };

}(jQuery));
