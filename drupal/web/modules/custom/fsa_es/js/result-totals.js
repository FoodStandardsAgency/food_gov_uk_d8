(function ($) {
  'use strict';

  Drupal.behaviors.fsaElasticsearchResultTotals = {
    attach: function (context, settings) {
      $.each(settings.fsa_es.result_totals, function(selector, value) {
        var result_total_value = '';

        if (value.keywords) {
          result_total_value = Drupal.formatPlural(
            value.total,
            '<strong>@count</strong> result found for %keywords',
            '<strong>@count</strong> results found for %keywords',
            {'@count': value.total, '%keywords': value.keywords}
          );
        }
        else {
          result_total_value = Drupal.formatPlural(
            value.total,
            '<strong>@count</strong> result found',
            '<strong>@count</strong> results found',
            {'@count': value.total}
          );
        }

        $(selector).html(result_total_value);
      });
    }
  };

}(jQuery));
