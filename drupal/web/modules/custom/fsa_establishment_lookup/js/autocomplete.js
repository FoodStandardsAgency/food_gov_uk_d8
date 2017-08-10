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

                // Get the postal code from selected google places entry.
                google.maps.event.addListener(autocomplete, 'place_changed', function() {
                    var place = autocomplete.getPlace();
                    for (var i = 0; i < place.address_components.length; i++) {
                        for (var j = 0; j < place.address_components[i].types.length; j++) {
                            if (place.address_components[i].types[j] === 'postal_code') {
                                // @todo: save this somewhere to map to an LA.
                                console.log(place.address_components[i].long_name);
                            }
                        }
                    }
                })
            }
            google.maps.event.addDomListener(window, 'load', init_places_autocomplete);

        }
    };

})(jQuery, Drupal, drupalSettings);
