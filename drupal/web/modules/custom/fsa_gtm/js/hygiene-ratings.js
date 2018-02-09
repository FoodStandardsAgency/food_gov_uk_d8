/**
 * @file
 * Exposes hygiene ratings search data to data layer.
 */

(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.fsaRatingsDataLayer = {
    attach: function (context, settings) {

      // Apply data layer push behaviour once.
      $(document, context).once('data-layer').each(function () {
        var keywords = drupalSettings.fsa_ratings.data_layer.keywords;
        var filters = drupalSettings.fsa_ratings.data_layer.filters;
        var pages = drupalSettings.fsa_ratings.data_layer.pages;
        var hits = drupalSettings.fsa_ratings.data_layer.hits;

        // Push forms that are not empty to data layer.
        if (
          keywords ||
          filters.business_type ||
          filters.fhis_rating_value ||
          filters.fhrs_rating_value ||
          filters.local_authority
        ) {
          dataLayer.push({
            "event": "search",
            "search": {
              "keywords": keywords == null ? undefined : keywords,
              "category": "hygiene-ratings",
              "results": hits.split(" ").join(""),
              "resultsPage": pages,
              "tags": {
                "businessType": filters.business_type == undefined ? null : filters.business_type,
                "hygieneStatus": filters.fhis_rating_value == undefined ? null : filters.fhis_rating_value,
                "hygieneRating": filters.fhrs_rating_value == undefined ? null : filters.fhrs_rating_value,
                "localAuthority": filters.local_authority == undefined ? null : filters.local_authority
              }
            }
          });
        }
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
