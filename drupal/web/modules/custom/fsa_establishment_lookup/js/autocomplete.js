(function ($, Drupal, drupalSettings) {

    'use strict';

    Drupal.behaviors.mybehavior = {
        attach: function (context, settings) {

            function init_places_autocomplete() {
                var input = document.getElementById(drupalSettings.fsa_establishment_lookup.googleplaces.element_id);
                var options = {
                    componentRestrictions: {
                        country: "gb"
                    }
                };
                var autocomplete = new google.maps.places.Autocomplete(input, options);
                autocomplete.setTypes(['establishment']);
            }
            google.maps.event.addDomListener(window, 'load', init_places_autocomplete);

        }
    };

})(jQuery, Drupal, drupalSettings);

