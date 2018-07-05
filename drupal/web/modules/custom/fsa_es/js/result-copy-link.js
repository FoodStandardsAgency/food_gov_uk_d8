(function ($) {
  'use strict';

  Drupal.behaviors.fsaElasticsearchResultCopyLink = {
    attach: function (context, settings) {

      $('.navigation-side .views-exposed-form', context).each(function() {
          var filters = $(this);

          // Prepare copy link.
          var copy_link = $('<a/>')
              .attr('href', '#')
              .addClass('copy-link__link')
              .text(Drupal.t('Copy results URL'))
              .on('click', function(e) {
                e.preventDefault();
              });

          // Insert copy link.
          $('.form-actions', this).after(copy_link);

          // Add copy functionality to link.
          var copy_link_clipboard = new ClipboardJS(copy_link[0], {
            text: function() {
              // Prepare link URL parts.
              var current_url = [location.protocol, '//', location.host, location.pathname].join('');
              var current_query_parts = [];

              // Collect all checked checkboxes and add their names and values to current query.
              $('input.form-checkbox:checked', filters).each(function() {
                current_query_parts.push($(this).attr('name') + '=' + $(this).attr('value'));
              });

              if (current_query_parts.length > 0) {
                var current_query = '?' + current_query_parts.join('&');
                current_query = encodeURI(current_query);
                return current_url + current_query;
              }
              else {
                return current_url;
              }

            }
          });

          // Notify success.
          copy_link_clipboard.on('success', function(e) {
            var success_class_name = 'link__success';
            $(e.trigger).addClass(success_class_name);
            setTimeout(
                function() {
                  $(e.trigger).removeClass(success_class_name);
                }, 2000);
          });
        });

    }
  };

}(jQuery));
