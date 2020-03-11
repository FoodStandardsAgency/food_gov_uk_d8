(function ($, Drupal, drupalSettings) {

    'use strict';

    var map,
        markersArray = [],
        autocomplete,
        geocoder,
        infoWindow,
        infoWindowsArray = [],
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

            // Init Geocoder service object.
            geocoder = new google.maps.Geocoder;

            // Init Geocoder and InfoWindow objects.
            infoWindow = new google.maps.InfoWindow;

            // Adds Google maps event listener to place marker.
            map.addListener('click', _this.handleMapClick);
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

            // Check if map hasn't already been initialised.
            if (typeof map === 'undefined') {
                _this.initMap();
            }

            var place = autocomplete.getPlace();
            geocoder.geocode({'placeId': place.place_id}, function (results, status) {
                if (status === 'OK') {
                    if (results[0]) {
                        // Remove previous markers.
                        _this.removeMapMarkers();

                        // Show map and create markers.
                        $('#map').show();
                        map.setZoom(17);
                        map.setCenter(results[0].geometry.location);

                        // Add marker to map.
                        var marker = _this.addMapMarker(results[0].geometry.location);

                        // Set info window content.
                        var infoContent = _this.formatInfoWindowAddress(place.name, place.address_components);
                        _this.addInfoBox(marker, infoContent);
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
        handleMapClick: function (event) {
            var _this = Drupal.behaviors.mybehavior;

            // Look up address of Google place and set address inputs.
            _this.getLatLngAddress(event).then(function (results) {
                // Removes previous markers.
                _this.removeMapMarkers();

                // Add marker to map.
                var marker = _this.addMapMarker(results[0].geometry.location);

                // Set info window content if user hasn't clicked on a place.
                if (!event.hasOwnProperty('placeId')) {
                    var infoContent = _this.formatInfoWindowAddress(null, results[0].address_components);
                    _this.addInfoBox(marker, infoContent);
                }

                // Set address input.
                var nameAddress = results[0].formatted_address;
                element_id = drupalSettings.fsa_establishment_lookup.googleplaces.element_id;
                $('#'+element_id).val(nameAddress);

                // Set hidden field with postcode.
                var postCode = _this.getPlacePostcode(results[0].address_components);
                $('input[data-drupal-selector="edit-fsa-establishment-postal-code"]').val(postCode);

            })
            .catch(function (error) {
                console.log(error.message);
            });
        },
        // Returns a promise while querying Google Places service.
        getLatLngAddress: function (event) {
            return new Promise(
                function (resolve, reject) {
                    // Query Google places API.
                    geocoder.geocode({'latLng': event.latLng}, function (results, status) {
                        if (status === google.maps.GeocoderStatus.OK) {
                            resolve(results);
                        }
                        else {
                            reject(new Error('Error occurred during place lookup.'))
                        }
                    });
                }
            );
        },
        // Adds a marker to the map.
        addMapMarker: function (latLng) {
            var marker = new google.maps.Marker({
                map: map,
                position: latLng
            });
            markersArray.push(marker);

            return marker;
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
        // Opens info box above marker.
        addInfoBox: function (marker, content) {
            var _this = Drupal.behaviors.mybehavior;

            var infoWindow = new google.maps.InfoWindow;
            infoWindow.setContent(content);
            infoWindow.open(map, marker);
            infoWindowsArray.push(infoWindow);

            return infoWindow;
        },
        // Formats marker address to match the format default markers use.
        formatInfoWindowAddress: function (name, address_components) {
            var _this = Drupal.behaviors.mybehavior,
                formattedAddress = '',
                numberStreet = '',
                borough = '',
                city = '',
                postCode ='';

            // Loop through address components and format parts.
            $(address_components).each(function (index, component) {
                switch (component.types[0]) {
                    case 'street_number':
                        numberStreet += component.long_name;
                        break;
                    case 'route':
                        numberStreet += ' ' + component.long_name;
                        break;
                    case 'neighborhood':
                        borough = component.long_name;
                        break;
                    case 'postal_town':
                        city = component.long_name;
                        break;
                    case 'postal_code':
                        postCode = component.long_name;
                        break;
                }
            });


            if (name !== null) {
                formattedAddress = '<strong>' + name + '</strong><br>';
            }

            formattedAddress += numberStreet + '<br>';

            if (borough !== '') {
                formattedAddress += borough + ', ' + city + '<br>';;
            }
            else {
                formattedAddress += city + '<br>';
            }

            formattedAddress += postCode;

            return formattedAddress;
        },
        // Extracts postcode from Google place object.
        getPlacePostcode: function (address_components) {
            $(address_components).each(function (index, component) {
                if (component.types[0] === 'postal_code') {
                    return component.long_name;
                }
            });
        }
    };

})(jQuery, Drupal, drupalSettings);
