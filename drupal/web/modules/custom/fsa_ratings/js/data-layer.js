/**
 * @file
 * Exposes search data to data layer.
 */

(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.fsaRatingsDataLayer = {
    attach: function (context, settings) {
      $('body', context).once('fsaRatingsDataLayer').each(function () {
        var keywords = drupalSettings.fsa_ratings.data_layer.keywords;
        var filters = drupalSettings.fsa_ratings.data_layer.filters;
        var pagerInfo = drupalSettings.fsa_ratings.data_layer.pager_info;
        var hitsTotal = drupalSettings.fsa_ratings.data_layer.hits_total;
        var data = {
          "event" : "search",
          "search": {
            "keywords" : keywords == null ? undefined : keywords,
            "category" : "hygiene ratings",
            "results" : hitsTotal,
            "resultsPage" : pagerInfo,
            "tags": {
              "businessType" : filters.business_type,
              "hygieneStatus": filters.fhis_rating_value,
              "hygieneRating": filters.fhrs_rating_value,
              "localAuthority": filters.local_authority
            }
          }
        };
        dataLayer.push(data);
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
