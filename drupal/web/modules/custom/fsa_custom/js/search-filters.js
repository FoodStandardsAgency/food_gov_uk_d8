(function ($) {
  $(window).bind('load', function() {
    // Grab the URL parameters and put in an array.
    var url_path = decodeURIComponent(window.location.search.substring(1));
    var clean_path = url_path.replace(/[\[\]']+/g, '');
    var clean_path = clean_path.replace(/_/g, '-');
    var path_params = clean_path.split('&');
    // Loop through the parameters.
    for (var i = 0; i < path_params.length; i++) {
      // For each parameter, grab the parameter name and value.
      var param = path_params[i].split('=');
      // Find the values in the page search filters and make them visible active.
      if ($('#fieldset-edit-'+param[0]).length) {
        $('#fieldset-edit-'+param[0]+' #edit-'+param[0]+' :input[value="'+param[1]+'"]').prop('checked', true);
        $('#edit-'+param[0]+'--wrapper legend').addClass('is-open');
        $('#fieldset-edit-'+param[0]+'').addClass('is-open');
      }
    }
  });
}(jQuery));
