(function ($) {
  'use strict';

  Drupal.behaviors.fsaElasticsearchResultTotals = {
    attach: function (context, settings) {
      var selector = '.views-result-total';

      $(selector).each(function() {
        var value = settings.fsa_es.result_totals;
        var result_total_value = '';

        if (value.keywords) {
          result_total_value = Drupal.formatPlural(
            value.total,
            '<span class="views-result-total__number">@count</span> result found for <span class="views-result-total__keyword">%keywords</span>',
            '<span class="views-result-total__number">@count</span> results found for <span class="views-result-total__keyword">%keywords</span>',
            {'@count': value.total, '%keywords': value.keywords}
          );
        }
        else {
          result_total_value = Drupal.formatPlural(
            value.total,
            '<span class="views-result-total__number">@count</span> result found',
            '<span class="views-result-total__number">@count</span> results found',
            {'@count': value.total}
          );
        }

        $(selector).html(result_total_value);
      });
    }
  };

}(jQuery));
