(function ($) {
  'use strict';
  Drupal.behaviors.fsaAddtoanyAccesibility = {
    attach: function (context, settings) {
      // Changing focus to the addtoany popup after clicking on "More..."
      $('.a2a_menu_show_more_less').on('click', function(){
        $('#a2apage_full .a2a_i:first-child').focus();
      });
    }
  };
}(jQuery));
