/**
 * @file
 * Exposes global search data to data layer.
 */

(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.fsaGtmDataLayer = {
    attach: function (context, settings) {

      // Get view identifier.
      var viewName = drupalSettings.fsa_ratings.data_layer.view_id;
      var viewID = '#views-exposed-form-' + viewName.replace(/_/g, "-") + '-page-1';
      console.log(viewName);

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
      if (!(viewName === "search_global_all" || viewName === "search_global_ratings_embed" || viewName === "search_news_alerts_all")) {
        data.search.tags = {};
      }

      // Extract category from views name and make namespace.
      var category = null;
      if (viewName.startsWith("search_global_")) {
        category = viewName.replace("search_global_", "global__");
      } else {
        category = viewName.replace("search_news_alerts_", "news_alerts__");
      }

      // Improve namespace__category and add to data.
      if (category === "global__ratings") {
        // Replace ratings category with hygiene-ratings.
        data.search.category = "global__hygiene_ratings";
      } else if (category === "global__ratings_embed") {
        // In decoupled mode view name is search_global_ratings_embed, rename for consistency.
        data.search.category = "global__all";
      } else {
        data.search.category = category;
      }

      // Push updated dataLayer on page load.
      $(document, context).once('search-page-load').each(function () {
        getDataSearchTerm();
        getDataHits();
        getDataPages();
        getDataFilters();
        dataLayer.push(data);
        // console.log(data);
      });

      // Push updated dataLayer on ajax success.
      $(document, context).once('search-ajax-load').ajaxSuccess(function(event, xhr, settings) {
        if (settings.url.startsWith('/views')) {
          getDataSearchTerm();
          getDataHits();
          getDataPages();
          getDataFilters();
          dataLayer.push(data);
          // console.log(data);
        }
      });

      // Add search term data from search form.
      function getDataSearchTerm() {
        var term = $("[id^=edit-keywords]").val();
        data.search.keywords = term ? term : undefined;
      }

      // Add hits and pages information to data from pager.
      function getDataHits() {
        data.search.results = '0';
        if ($(".search-result-page-count").length > 0) {
          var count = $(".search-result-page-count").text().trim().split(" ");
          var hits = count[count.length - 1];
          if (hits) {data.search.results = hits;}
        }
      }

      // Add hits and pages information to data from pager.
      function getDataPages() {
        data.search.resultsPage = '1 of 1';
        if ($(".pager__items").length > 0) {
          // Add pages to data.
          var isActiveQuery = $(".pager__item.is-active a").attr("href");
          var lastQuery = $(".pager__items li:nth-last-of-type(1) a").attr("href");
          if (isActiveQuery && lastQuery) {
            var pageNumber = parseInt(isActiveQuery.trim().split("=").pop()) + 1;
            var numberOfPages = parseInt(lastQuery.trim().split("=").pop()) + 1;
            data.search.resultsPage = pageNumber + " of " + numberOfPages;
          }
        }
      }

      // Add filter data from filters.
      function getDataFilters() {

        // Add all data when filters are used for any of five search tabs.
        switch(viewName) {
          case "search_global_guidance":
            $(viewID, context).each(function () {
              _checkboxes_to_data($("[id^=edit-audience]", this), 'guidanceAudience');
              _checkboxes_to_data($("[id^=edit-region]", this), 'nation');
            });
            break;

          case "search_global_ratings":
            data.search.tags.businessType = null;
            data.search.tags.localAuthority = null;

            $(viewID, context).each(function () {
              var selected;
              selected = $("[id^=edit-business-type] option:selected", this).val().toLowerCase();
              data.search.tags.businessType = selected == "all" ? null : selected;
              selected = $("[id^=edit-local-authority] option:selected", this).val().toLowerCase();
              data.search.tags.localAuthority = selected == "all" ? null : selected;

              _checkboxes_to_data($("[id^=edit-fhrs-rating-value]", this), 'hygieneRating');
              _checkboxes_to_data($("[id^=edit-fhis-rating-value]", this), 'hygieneStatus');
            });
            break;

          case "search_global_news_and_alerts":
            $(viewID, context).each(function () {
              _checkboxes_to_data($("[id^=edit-type]", this), 'newsType');
              _checkboxes_to_data($("[id^=edit-consultation-year]", this), 'consultationYear');
              _checkboxes_to_data($("[id^=edit-consultation-status]", this), 'consultationStatus');
              _checkboxes_to_data($("[id^=edit-consultation-responses]", this), 'consultationResponses');
              _checkboxes_to_data($("[id^=edit-region]", this), 'nation');
            });
            break;

          case "search_global_research":
            $(viewID, context).each(function () {
              _checkboxes_to_data($("[id^=edit-topic]", this), 'researchTopic');
              _checkboxes_to_data($("[id^=edit-region]", this), 'nation');
            });
            break;
        }
      }

      // Helper function to extract data from fieldset.
      function _checkboxes_to_data(fieldset, tagName) {
        var checked = {};
        data.search.tags[tagName] = null;

        var current = [];
        $('.form-checkbox:checked', fieldset).each(function() {
          current.push($(this).val().toLowerCase());
        });
        checked.current = current.join(",");
        data.search.tags[tagName] = checked.current ? checked.current : null;
      }
    }
  };
})(jQuery, Drupal, drupalSettings);
