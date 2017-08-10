function initialize() {
    var input = document.getElementById('edit-google-places-test-element'); // @todo: this is for testing, figure out how to control which field(s) should implement the autocomplete.
    var options = {
        componentRestrictions: {
            country: "gb"
        }
    };
    var autocomplete = new google.maps.places.Autocomplete(input, options);
    autocomplete.setTypes(['establishment']);
}
google.maps.event.addDomListener(window, 'load', initialize);