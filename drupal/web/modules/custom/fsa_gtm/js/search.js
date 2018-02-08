/**
 * @file
 * Exposes global search data to data layer.
 */

(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.fsaGtmDataLayer = {
    attach: function (context, settings) {

      // Get view identifier.
      var viewId = drupalSettings.fsa_ratings.data_layer.view_id;

      // Define one of two data objects: simple and full (with tags property).
      var data = {
        "event": "search",
        "search": {
          "keywords": undefined,
          "category": undefined,
          "results": undefined,
          "resultsPage": undefined
        }
      };
      if (viewId != "search_global_all") {
        data.search.tags = {};
      }

      // Add category to data (coerced from views identifier).
      var category = viewId.replace("search_global_", "");
      if (category == "ratings") {
        data.search.category = "hygiene-" + category;
      }
      else {
        data.search.category = category;
      }

      // Push search term on page load response.
      pushSearchTerm();

      // Push pager information on ajax response.
      $(document, context).once("data-layer-ajax").ajaxSuccess(function() {
        pushHitsAndPages();
        dataLayer.push(data);
      });

      // Add all data when filters are used for any of five search tabs.
      switch(viewId) {

        // Guidance search.
        case "search_global_guidance":

          // Initialise tags property.
          data.search.tags.guidanceAudience = null;
          data.search.tags.nation = null;

          // On change push to data layer.
          $("#views-exposed-form-search-global-guidance-page-1", context).change(function () {

            // Add search term to data
            pushSearchTerm();

            var checked = {};

            // Select filtered values and add to data
            var guidanceAudience = [];
            $("[id^=edit-audience] .form-checkbox:checked").each(function() {
              guidanceAudience.push($(this).val().toLowerCase());
            });
            checked.audience = guidanceAudience.join(",");
            data.search.tags.guidanceAudience = checked.audience ? checked.audience : null;

            var guidanceRegion = [];
            $("[id^=edit-region] .form-checkbox:checked").each(function() {
              guidanceRegion.push($(this).val().toLowerCase());
            });
            checked.region = guidanceRegion.join(",");
            data.search.tags.nation = checked.region ? checked.region : null;

            dataLayer.push(data);
          });
          break;

        // Ratings search - see comments for guidance search.
        case "search_global_ratings":

          data.search.tags.businessType = null;
          data.search.tags.localAuthority = null;
          data.search.tags.hygieneRating = null;
          data.search.tags.hygieneStatus = null;

          $("#views-exposed-form-search-global-ratings-page-1", context).change(function () {

            pushSearchTerm();

            var selected;

            selected = $("[id^=edit-business-type] option:selected").val().toLowerCase();
            data.search.tags.businessType = selected == "all" ? null : selected;

            selected = $("[id^=edit-local-authority] option:selected").val().toLowerCase();
            data.search.tags.localAuthority = selected == "all" ? null : selected;

            var checked = {};

            var hygieneRating = [];
            $("[id^=edit-fhrs-rating-value] .form-checkbox:checked").each(function() {
              hygieneRating.push($(this).val().toLowerCase());
            });
            checked.rating = hygieneRating.join(",");
            data.search.tags.hygieneRating = checked.rating ? checked.rating : null;

            var hygieneStatus = [];
            $("[id^=edit-fhis-rating-value] .form-checkbox:checked").each(function() {
              hygieneStatus.push($(this).val().toLowerCase());
            });
            checked.status = hygieneStatus.join(",");
            data.search.tags.hygieneStatus = checked.status ? checked.status : null;

            dataLayer.push(data);
          });
          break;

        // News & alerts search - see comments for guidance search.
        case "search_global_news_and_alerts":

          data.search.tags.newsType = null;
          data.search.tags.nation = null;

          $("#views-exposed-form-search-global-news-and-alerts-page-1", context).change(function () {

            pushSearchTerm();

            var checked = {};

            var newsType = [];
            $("[id^=edit-local-type] .form-checkbox:checked").each(function() {
              newsType.push($(this).val().toLowerCase());
            });
            checked.type = newsType.join(",");
            data.search.tags.newsType = checked.type ? checked.type : null;

            var nation = [];
            $("[id^=edit-region] .form-checkbox:checked").each(function() {
              nation.push($(this).val().toLowerCase());
            });
            checked.nation = nation.join(",");
            data.search.tags.nation = checked.nation ? checked.nation : null;

            dataLayer.push(data);
          });
          break;

        // Research search - see comments for guidance search.
        case "search_global_research":

          data.search.tags.researchTopic = null;
          data.search.tags.nation = null;

          $("#views-exposed-form-search-global-research-page-1", context).change(function () {

            pushSearchTerm();

            var checked = {};

            var researchTopic = [];
            $("[id^=edit-topic] .form-checkbox:checked").each(function() {
              researchTopic.push($(this).val().toLowerCase());
            });
            checked.topic = researchTopic.join(",");
            data.search.tags.researchTopic = checked.topic ? checked.topic : null;

            var nation = [];
            $("[id^=edit-region] .form-checkbox:checked").each(function() {
              nation.push($(this).val().toLowerCase());
            });
            checked.nation = nation.join(",");
            data.search.tags.nation = checked.nation ? checked.nation : null;

            dataLayer.push(data);
          });
          break;
      }

      // Add search term data.
      function pushSearchTerm() {
        var term = $("[id^=edit-keywords]").val();
        data.search.keywords = term ? term : undefined;
      }

      // Add hits and pages information to data from pager.
      function pushHitsAndPages() {

        // Add hits to data.
        var hits = $(".listing footer").text().trim().split(" ")[3].replace(/[^0-9]/, "");
        if (hits) {
          data.search.results = hits;
        }

        // Add pages to data.
        isActiveQuery = $(".pager__item.is-active a").attr("href");
        lastQuery = $(".pager__item--last a").attr("href");
        if (isActiveQuery && lastQuery) {
          var pageNumber = isActiveQuery.trim().split("=").pop();
          var numberOfPages = lastQuery.trim().split("=").pop();
          data.search.resultsPage = pageNumber + "-" + numberOfPages;
        }
      }
    }
  };
})(jQuery, Drupal, drupalSettings);
