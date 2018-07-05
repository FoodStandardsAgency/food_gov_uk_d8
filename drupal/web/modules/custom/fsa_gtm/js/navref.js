/**
 * @file
 * Adds navref query to search result links.
 */

(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.fsaGtmNavref = {
    attach: function (context, settings) {

      // Get view identifier.
      var viewId = drupalSettings.fsa_ratings.data_layer.view_id;

      $('.views-field article', context).each(function() {
        var article = $(this);
        var link = $('a[rel="bookmark"]', article);
        link.attr('href', link.attr('href') + '?navref=' + viewId + '_' + article.data('search-result-counter'));
      });

    }
  };
})(jQuery, Drupal, drupalSettings);
