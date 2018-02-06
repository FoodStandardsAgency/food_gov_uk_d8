/**
 * @file
 * Exposes global search data to data layer.
 */

(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.fsaCustomDataLayer = {
    attach: function (context, settings) {
      $('main', context).once('fsaCustomDataLayer').each(function () { // once('fsaCustomDataLayer', function () {

        // Apply data layer push behaviour once.
        var category = drupalSettings.fsa_ratings.data_layer.category;
        var filters = drupalSettings.fsa_ratings.data_layer.filters;
        var pager = drupalSettings.fsa_ratings.data_layer.pager;


        // Push data relevant to the search category in use.
        switch(category) {

          // Search all.
          case "search all":
            dataLayer.push({
              "event": "search",
              "search": {
                "keywords" : filters.keyword == null ? undefined : filters.keyword,
                "category": "search all"
              }
            });
            console.log(dataLayer[2]["search"]);
            break;

          // Search guidance.
          case "search guidance":
            dataLayer.push({
              "event": "search",
              "search": {
                "keywords": filters.keyword == null ? undefined : filters.keyword,
                "category": "search guidance",
                "results": pager.total_items,
                "resultsPage": pager.page_of_pages,
                "tags": {
                  "guidanceAudience": filters.guidance_audience,
                  "nation": filters.nation
                }
              }
            });
            console.log(dataLayer[2]["search"]);
            break;

          // Search ratings.
          case "search ratings":
            dataLayer.push({
              "event": "search",
              "search": {
                "keywords": filters.keyword == null ? undefined : filters.keyword,
                "category": "search ratings",
                "tags": {
                  "businessType": filters.business_type,
                  "localAuthority": filters.ratings_local_authority,
                  "hygieneRating": filters.ratings_fhrs_rating_value,
                  "hygieneStatus": filters.ratings_fhis_rating_value
                }
              }
            });
            console.log(dataLayer[2]["search"]);
            break;

          // Search news and alerts.
          case "search news and alerts":
            dataLayer.push({
              "event": "search",
              "search": {
                "keywords": filters.keyword == null ? undefined : filters.keyword,
                "category": "search news and alerts",
                "tags": {
                  "newsType": filters.news_type,
                  "nation": filters.nation
                }
              }
            });
            console.log(dataLayer[2]["search"]);
            break;

          // Search research.
          case "search research":
            dataLayer.push({
              "event": "search",
              "search": {
                "keywords": filters.keyword == null ? undefined : filters.keyword,
                "category": "search research",
              },
              "tags": {
                "researchTopic": filters.research_topic,
                "nation": filters.nation
              }
            });
            console.log(dataLayer[2]["search"]);
            break;
        }
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
