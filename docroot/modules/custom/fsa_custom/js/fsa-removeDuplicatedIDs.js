(function ($) {
  'use strict';
  Drupal.behaviors.fsaRemoveDuplicatedIDs = {
    attach: function (context, settings) {
      // Removing duplicated IDs.
      const ids = [
        'block-search-keyword',
        'edit-keywords',
        'edit-fax',
        'search-keyword',
        'edit-actions',
        'edit-fax--2',
        'edit-hp',
        'edit-hp--2',
      ];

      ids.forEach(function(existingid) {
        var index = 1;
        $('#' + existingid).each(function () {
          var newid = '#' + existingid + '--' + index;
          // Make sure the new id is non-existent.
          while ($(newid).length > 0) {
            newid = '#' + existingid + '--' + ++index;
          }
          $(this).attr('id', existingid + '--' + index);
          // Modify label pointing to this item, update reference to its item.
          $(this).siblings("label[for='" + existingid + "']")
            .attr('for', existingid + '--' + index);
        });
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
