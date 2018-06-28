(function ($) {
  'use strict';
  Drupal.behaviors.fsaSearchFilters = {
    attach: function (context, settings) {
      $(window).bind('load', function () {
        if ($('body').hasClass('theme--search')) {
          // Grab the URL parameters and put in an array.
          var urlPath = decodeURIComponent(window.location.search.substring(1));
          var cleanPath = urlPath.replace(/[\[\]']+/g, '');
          var cleanPath = cleanPath.replace(/_/g, '-');
          var pathParams = cleanPath.split('&');
          // Loop through the parameters.
          for (var i = 0; i < pathParams.length; i++) {
            // For each parameter, grab the parameter name and value.
            var param = pathParams[i].split('=');
            // Find the values in the page search filters and make them active.
            if ($('#fieldset-edit-' + param[0]).length) {
              $('#fieldset-edit-' + param[0] + ' #edit-' + param[0] + ' :input[value="' + param[1] + '"]').prop('checked', true);
              $('#edit-' + param[0] + '--wrapper legend').addClass('is-open');
              $('#fieldset-edit-' + param[0]).addClass('is-open');
            }
          }
        }
      });
    }
  };
}(jQuery));
