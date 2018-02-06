/**
 * @file
 * Exposes hygiene ratings search data to data layer.
 */

(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.fsaRatingsDataLayer = {
    attach: function (context, settings) {
      $('main', context).once('data-layer').each(function () {

        // Apply data layer push behaviour once.
        var keywords = drupalSettings.fsa_ratings.data_layer.keywords;
        var filters = drupalSettings.fsa_ratings.data_layer.filters;
        var pagerInfo = drupalSettings.fsa_ratings.data_layer.pager_info;
        var hitsTotal = drupalSettings.fsa_ratings.data_layer.hits_total;
        dataLayer.push({
          "event" : "search",
          "search": {
            "keywords" : keywords == null ? undefined : keywords,
            "category" : "hygiene-ratings",
            "results" : hitsTotal.split(" ").join(''),
            "resultsPage" : pagerInfo,
            "tags": {
              "businessType" : filters.business_type,
              "hygieneStatus": filters.fhis_rating_value,
              "hygieneRating": filters.fhrs_rating_value,
              "localAuthority": filters.local_authority
            }
          }
        });
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
