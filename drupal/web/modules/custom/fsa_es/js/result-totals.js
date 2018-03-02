(function ($) {
  'use strict';

  Drupal.behaviors.fsaElasticsearchResultTotals = {
    attach: function (context, settings) {
      var selector = '.views-result-total';
      var value = settings.fsa_es.result_totals;
      var keywords = value.keywords || null;
      var total_main_view = value.total.main_view;
      var total_ratings = value.total.ratings || null;
      var class_number = 'views-result-total__number';
      var class_keyword = 'views-result-total__keyword';
      var result_string = '';

      /**
       * Returns "x page" or "x pages" for pages/ratings combo string.
       *
       * @param total
       * @returns {string}
       */
      var getComboMainViewString = function(total) {
        return Drupal.formatPlural(
          total,
          '<span class="@class_number">@count</span> page',
          '<span class="@class_number">@count</span> pages',
          {'@class_number': class_number}
        );
      };

      /**
       * Returns "x rating" or "x ratings" for pages/ratings combo string.
       *
       * @param total
       * @returns {string}
       */
      var getComboRatingsString = function(total) {
        return Drupal.formatPlural(
          total,
          '<span class="@class_number">@count</span> food hygiene ratings result',
          '<span class="@class_number">@count</span> food hygiene ratings results',
          {'@class_number': class_number}
        );
      };

      /**
       * Returns "x result found" or "x results found" for simple (not combined result) string.
       *
       * @param total
       * @returns {string}
       */
      var getMainViewString = function(total) {
        return Drupal.formatPlural(
          total,
          '<span class="@class_number">@count</span> result found',
          '<span class="@class_number">@count</span> results found',
          {'@class_number': class_number}
        );
      };

      /**
       * Returns "x result found for y" or "x results found for y" for simple (not combined result) string with keyword.
       *
       * @param total
       * @returns {string}
       */
      var getMainViewWithKeywordsString = function(total, keywords) {
        // A bit of cheating. Drupal will scramble strings for translations only if they are wrapped in Drupal.t().
        //
        return Drupal.formatPlural(
          total,
          '<span class="@class_number">@count</span> result found for <span class="@class_keyword">%keywords</span>',
          '<span class="@class_number">@count</span> results found for <span class="@class_keyword">%keywords</span>',
          {'%keywords': keywords, '@class_number': class_number, '@class_keyword': class_keyword}
        );
      };

      $(selector).once('views-result-totals').each(function() {
        if (keywords) {
          // Show main view and ratings totals in combo.
          if (total_main_view && total_ratings) {
            result_string = Drupal.t('!main_view_string and !ratings_string found for <span class="@class_keyword">%keywords</span>',
              {'!main_view_string': getComboMainViewString(total_main_view), '!ratings_string': getComboRatingsString(total_ratings), '%keywords': keywords, '@class_keyword': class_keyword}
            );
          }
          // Show only ratings totals with keywords.
          else if (total_ratings) {
            result_string = Drupal.t('!ratings_string found for <span class="@class_keyword">%keywords</span>',
              {'!ratings_string': getComboRatingsString(total_ratings), '%keywords': keywords, '@class_keyword': class_keyword}
            );
          }
          // Show only main view totals with keywords.
          else {
            result_string = getMainViewWithKeywordsString(total_main_view, keywords);
          }
        }
        else {
          // Show main view and ratings totals in combo.
          if (total_main_view && total_ratings) {
            result_string = Drupal.t('!main_view_string and !ratings_string found',
              {'!main_view_string': getComboMainViewString(total_main_view), '!ratings_string': getComboRatingsString(total_ratings)}
            );
          }
          // Show only ratings totals.
          else if (total_ratings) {
            result_string = Drupal.t('!ratings_string found',
              {'!ratings_string': getComboRatingsString(total_ratings)}
            );
          }
          // Show only main view totals.
          else {
            result_string = getMainViewString(total_main_view);
          }
        }

        $(selector).html(result_string);
      });
    }
  };

}(jQuery));
