(function ($, Drupal, drupalSettings) {

    'use strict';

    var map,
        markersArray = [],
        autocomplete,
        element_id;

    Drupal.behaviors.mybehavior = {
        attach: function (context, settings) {

            var _this = this;

            // Get autocomplete input ID from settings.
            element_id = drupalSettings.fsa_establishment_lookup.googleplaces.element_id;
            var input = document.getElementById(element_id);

            // Init Google geolocation autocomplete input.
            autocomplete = new google.maps.places.Autocomplete(input, {
                componentRestrictions: {
                    country: 'gb'
                }
            });
            autocomplete.setTypes(['establishment']);

            // Triggers behaviour when autocomplete used.
            google.maps.event.addListener(autocomplete, 'place_changed', _this.handleAutocompleteChange);

            // Triggers Google map on CTA click.
            $('a#map-trigger').click(function (e) {
                e.preventDefault();

                // Check if map hasn't already been initialised.
                if (typeof map === 'undefined') {
                    _this.initMap();
                }

                // Default to users location with their permission.
                _this.detectUserLocation();

                // Show map.
                $('#map').show();
            });
        },
        // Initialises google maps object when map shown or autocomplete used.
        initMap: function () {
            var _this = Drupal.behaviors.mybehavior;

            // Show map and set default location to London.
            map =  new google.maps.Map(document.getElementById('map'), {
                center: {lat: 51.5074, lng: 0.1278},
                mapTypeControl: false,
                zoom: 15
            });

            // Adds Google maps event listener to place marker.
            map.addListener('click', _this.handlePlaceClick);
        },
        // Detect user location.
        detectUserLocation: function () {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function (position) {
                    var pos = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    map.setCenter(pos);
                    map.setZoom(15);
                });
            }
        },
        // Sets markers and infobox on map when address autocomplete is changed.
        handleAutocompleteChange: function () {
            var _this = Drupal.behaviors.mybehavior;

            // Init Geocoder and InfoWindow objects.
            var geocoder = new google.maps.Geocoder;
            var infowindow = new google.maps.InfoWindow;

            var place = autocomplete.getPlace();
            geocoder.geocode({'placeId': place.place_id}, function (results, status) {
                if (status === 'OK') {
                    if (results[0]) {
                        // Check if map hasn't already been initialised.
                        if (typeof map === 'undefined') {
                            _this.initMap();
                        }

                        // Remove previous markers.
                        _this.removeMapMarkers();

                        // Show map and create markers.
                        $('#map').show();
                        map.setZoom(17);
                        map.setCenter(results[0].geometry.location);
                        var marker = new google.maps.Marker({
                            map: map,
                            position: results[0].geometry.location
                        });
                        markersArray.push(marker);

                        // Set info window content.
                        var infoContent = _this.formatInfoWindowAddress(place);
                        infowindow.setContent(infoContent);
                        infowindow.open(map, marker);
                    }
                }
            });

            // Set address input.
            var nameAddress = place.name + ', ' + place.formatted_address;
            $('#'+element_id).val(nameAddress);

            // Set hidden field with postcode.
            // @todo: instead of creating the hidden field via configs extend the custom webform plugin.
            var postCode = _this.getPlacePostcode(place);
            $('input[data-drupal-selector="edit-fsa-establishment-postal-code"]').val(postCode);

        },
        // Event handler for clicking Google place within map.
        handlePlaceClick: function (event) {
            var _this = Drupal.behaviors.mybehavior;

            // Only place markers for actual Google places.
            if (!event.hasOwnProperty('placeId')) {
                return false;
            }

            // Look up address of Google place and set address inputs.
            _this.getPlaceAddress(event.placeId).then(function (place) {
                // Removes previous markers.
                _this.removeMapMarkers();

                // Add new marker.
                var marker = new google.maps.Marker({
                    map: map,
                    position: event.latLng
                });
                markersArray.push(marker);

                // Set address input.
                var nameAddress = place.name + ', ' + place.formatted_address;
                element_id = drupalSettings.fsa_establishment_lookup.googleplaces.element_id;
                $('#'+element_id).val(nameAddress);

                // Set hidden field with postcode.
                var postCode = _this.getPlacePostcode(place);
                $('input[data-drupal-selector="edit-fsa-establishment-postal-code"]').val(postCode);

            })
            .catch(function (error) {
                console.log(error.message);
            });
        },
        // Returns a promise while querying Google Places service.
        getPlaceAddress: function (placeId) {
            return new Promise(
                function (resolve, reject) {
                    // Google places API request object.
                    var request = {
                        placeId: placeId,
                        fields: ['address_component', 'formatted_address', 'name']
                    };

                    // Query Google places API.
                    var service = new google.maps.places.PlacesService(map);
                    service.getDetails(request, function (place, status) {
                        if (status === google.maps.places.PlacesServiceStatus.OK) {
                            resolve(place);
                        }
                        else {
                            reject(new Error('Error occurred during place lookup.'))
                        }
                    });
                }
            );
        },
        // Removes all markers from Google map.
        removeMapMarkers: function () {
            if (markersArray) {
                for (var i in markersArray) {
                    markersArray[i].setMap(null);
                }
                markersArray.length = 0;
            }
        },
        // Formats marker address to match the format default markers use.
        formatInfoWindowAddress: function (place) {
            var _this = Drupal.behaviors.mybehavior,
                formattedAddress,
                numberStreet = '',
                boroughCity = '',
                postCode ='';

            // Loop through address components and format parts.
            $(place.address_components).each(function (index, component) {
                switch (component.types[0]) {
                    case 'street_number':
                        numberStreet += component.long_name;
                        break;
                    case 'route':
                        numberStreet += ' ' + component.long_name;
                        break;
                    case 'neighborhood':
                        boroughCity += component.long_name;
                        break;
                    case 'postal_town':
                        boroughCity += ', ' + component.long_name;
                        break;
                    case 'postal_code':
                        postCode = component.long_name;
                        break;
                }
            });
            formattedAddress = '<strong>' + place.name + '</strong><br>';
            formattedAddress += numberStreet + '<br>';
            formattedAddress += boroughCity + '<br>';
            formattedAddress += postCode;

            return formattedAddress;
        },
        // Extracts postcode from Google place object.
        getPlacePostcode: function (place) {
            $(place.address_components).each(function (index, component) {
                if (component.types[0] === 'postal_code') {
                    return component.long_name;
                }
            });
        }
    };

})(jQuery, Drupal, drupalSettings);
