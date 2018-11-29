/**
 * @file
 * Adds navref query to search result links.
 */

(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.fsaGtmNavref = {
    attach: function (context, settings) {

      // Get view identifier.
      var viewName = drupalSettings.fsa_ratings.data_layer.view_id;
      var viewID = viewName.replace(/_/g, "-");

      if (viewID === 'search-global-ratings-embed') {
        viewID = 'search-global-all';
      }

      $('.views-field article', context).each(function() {
        var article = $(this);
        var link = $('a[rel="bookmark"]', article);
        var count = article.data('search-result-counter');
        if (typeof count === 'undefined') {
          var count = 0;
        }
        link.attr('href', link.attr('href') + '?navref=' + viewID + '-' + count);
      });

    }
  };
})(jQuery, Drupal, drupalSettings);
