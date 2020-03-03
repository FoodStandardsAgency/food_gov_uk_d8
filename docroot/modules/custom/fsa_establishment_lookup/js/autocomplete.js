(function ($, Drupal, drupalSettings) {

    'use strict';

    var map;

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
                            switch (place.address_components[i].types[j]) {
                                case  'postal_code':
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

                    // Set postal code to respective field.
                    // @todo: instead of creating the hidden field via configs extend the custom webform plugin.
                    $('input[data-drupal-selector="edit-fsa-establishment-postal-code"]').val(postal_code)

                });
            }
            google.maps.event.addDomListener(window, 'load', init_places_autocomplete);

            // Triggers Google map on CTA click.
            var _this = this;
            $('a#map-trigger').click(function(e) {
                e.preventDefault();

                // Init map.
                map = _this.initMap();

                // Adds Google maps event listener to place marker.
                map.addListener('click', _this.placeMarker);
            });
        },
        initMap: function() {
            // Show map and set default location to London.
            $('#map').show();
            var map = new google.maps.Map(document.getElementById('map'), {
                center: {lat: 51.5074, lng: 0.1278},
                zoom: 10
            });

            // If user allows address access then set their position on map.
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    var pos = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    map.setCenter(pos);
                    map.setZoom(14);
                });
            }

            return map;
        },
        placeMarker: function(event) {
            var _this = Drupal.behaviors.mybehavior;

            // Only place markers for actual Google places.
            if (!event.hasOwnProperty('placeId')) {
                return false;
            }

            // Look up address of Google place and set address inputs.
            _this.getPlaceAddress(event.placeId).then(function(place) {
                var element_id = drupalSettings.fsa_establishment_lookup.googleplaces.element_id;

                // Set address input.
                var nameAddress = place.name + ', ' + place.formatted_address;
                $('#'+element_id).val(nameAddress);

                // Loop through address components and extract postcode.
                $(place.address_components).each(function(index, component) {
                    if (component.types[0] === 'postal_code') {
                        // Set hidden field with postcode value.
                        $('input[data-drupal-selector="edit-fsa-establishment-postal-code"]').val(component.long_name)
                    }
                });
            })
            .catch(function (error) {
                console.log(error.message);
            });
        },
        getPlaceAddress: function (placeId) {
            return new Promise(
                function(resolve, reject) {
                    // Google places API request object.
                    var request = {
                        placeId: placeId,
                        fields: ['address_component', 'formatted_address', 'name']
                    };

                    // Query Google places API.
                    var service = new google.maps.places.PlacesService(map);
                    service.getDetails(request, function(place, status) {
                        if (status === google.maps.places.PlacesServiceStatus.OK) {
                            resolve(place);
                        }
                        else {
                            reject(new Error('Error occurred during place lookup.'))
                        }
                    });
                }
            );
        }
    };

})(jQuery, Drupal, drupalSettings);
