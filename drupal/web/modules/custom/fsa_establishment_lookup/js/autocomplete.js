(function ($, Drupal, drupalSettings) {

    'use strict';

    Drupal.behaviors.mybehavior = {
        attach: function (context, settings) {

            function init_places_autocomplete() {
                var element_id = drupalSettings.fsa_establishment_lookup.googleplaces.element_id;
                var input = document.getElementById(element_id);
                var options = {
                    componentRestrictions: {
                        country: "gb"
                    }
                };
                var autocomplete = new google.maps.places.Autocomplete(input, options);
                autocomplete.setTypes(['establishment']);

                // Get the postal code from selected google places entry.
                google.maps.event.addListener(autocomplete, 'place_changed', function() {
                    var postal_code = '',
                        address = '',
                        postal_town = '';
                    var place = autocomplete.getPlace(),
                        establishment = place.name;
                    for (var i = 0; i < place.address_components.length; i++) {
                        for (var j = 0; j < place.address_components[i].types.length; j++) {
                            console.log(place.address_components[i].types[j]);
                            switch (place.address_components[i].types[j]) {
                                case  'postal_code':
                                    // @todo: save this somewhere to map to an LA.
                                    postal_code = place.address_components[i].long_name;
                                    break;
                                case 'route':
                                    address = place.address_components[i].long_name;
                                    break;
                                case 'postal_town':
                                    postal_town = place.address_components[i].long_name;
                                    break;
                            }
                        }
                    }

                    // Replace field values with more details of the establishment.
                    $('#'+element_id).val(establishment+', ' +address+', ' +postal_code+', '+postal_town);

                    // And set postal code to a field for searching the local authority.
                    // @todo: craete the field + functionality to search for the LA.
                    $('#postal-code-for-la-search').val(postal_code);
                });
            }
            google.maps.event.addDomListener(window, 'load', init_places_autocomplete);

        }
    };

})(jQuery, Drupal, drupalSettings);
