(function ($) {
  'use strict';
  var waitForEl = function(selector, callback) {
    if (jQuery(selector).length) {
      callback();
    } else {
      setTimeout(function() {
        waitForEl(selector, callback);
      }, 100);
    }
  };

  Drupal.behaviors.fsaAddtoanyAccesibility = {
    attach: function (context, settings) {
      // Changing focus to the addtoany popup after clicking on "More..."
      // We can't seem to use a live event here.
      waitForEl('#a2apage_show_more_less', function () {
        $('#a2apage_show_more_less').click(function (e) {
          setTimeout(function () {
            $('#a2apage_full .a2a_i:first-child').focus();
          }, 100);
        });
      });
    }
  };

  Drupal.behaviors.fsaHeaderPadding = {
    attach: function (context, settings) {
      var resizeTimer;
      headerPadding();

      $(window).on('resize', function(e) {
        clearTimeout(resizeTimer);

        resizeTimer = setTimeout(function() {
          headerPadding();
        }, 250);
      });

      function headerPadding() {
        var width = $(window).width();
        if ($('.layout__content--header').length > 0) {
          // Adding padding if it's a desktop width.
          if (width > 960) {
            var titleHeight = $('.block-page-title').innerHeight();
            var headerHeight = $('#block-page-content-header').innerHeight();
            $('.layout__content--main.layout__content--with-header').css('padding-top', titleHeight + headerHeight + 100);
          }
          // Removing it if it's not, in case we've added it before.
          else {
            $('.layout__content--main.layout__content--with-header').css('padding-top', '');
          }
        }
      }

    }
  };
}(jQuery));
