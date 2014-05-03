/*
 * Loaction jQuery plugin
 * version 0.1
 *
 * Copyright (c) 2014 Cyril Adelekan (Cyrilinc.co.uk)
 *
 * Dual licensed under the MIT and GPL (LICENSE.txt)
 *
 * http://Cyrilinc.co.uk/Snowflakes/
 *
 */

var latlong;
function initialize() {
    
    if (typeof preLat === 'undefined' || typeof preLng === 'undefined') {
        preLat = 51.5072;
        preLng = 0.1275;
    }

    //alert(preLat + " => "+ preLng);
    var mapOptions = {
        center: new google.maps.LatLng(preLat, preLng),
        zoom: 13
    };
    var map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);

    var input = /** @type {HTMLInputElement} */(document.getElementById('pac-input'));

    var types = document.getElementById('type-selector');
    map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
    map.controls[google.maps.ControlPosition.TOP_LEFT].push(types);

    var autocomplete = new google.maps.places.Autocomplete(input);
    autocomplete.bindTo('bounds', map);

    var infowindow = new google.maps.InfoWindow();
    var marker = new google.maps.Marker({
        map: map
    });

    google.maps.event.addListener(autocomplete, 'place_changed', function() {
        infowindow.close();
        marker.setVisible(false);
        var place = autocomplete.getPlace();
        if (!place.geometry) {
            return;
        }

        // If the place has a geometry, then present it on a map.
        if (place.geometry.viewport) {
            map.fitBounds(place.geometry.viewport);
        } else {
            map.setCenter(place.geometry.location);
            map.setZoom(13);  
        }
        marker.setIcon(/** @type {google.maps.Icon} */({
            url: place.icon,
            size: new google.maps.Size(71, 71),
            origin: new google.maps.Point(0, 0),
            anchor: new google.maps.Point(17, 34),
            scaledSize: new google.maps.Size(35, 35)
        }));
        marker.setPosition(place.geometry.location);
        marker.setVisible(true);

        var address = '';
        if (place.address_components) {
            address = [
                (place.address_components[0] && place.address_components[0].short_name || ''),
                (place.address_components[1] && place.address_components[1].short_name || ''),
                (place.address_components[2] && place.address_components[2].short_name || '')
            ].join(' ');
        }
        
        latlong = place.geometry.location.lat() + ',' + place.geometry.location.lng();
        
        if(latlong===',' || latlong==='')
            latlong = preLat + ',' + preLng;
        
       
        document.getElementById("latlong").value = latlong;
        infowindow.setContent('<div><strong>' + place.name + '</strong><br>' + address);
        infowindow.open(map, marker);
    });

    // Sets a listener on a radio button to change the filter type on Places
    // Autocomplete.
    function setupClickListener(id, types) {
        var radioButton = document.getElementById(id);
        google.maps.event.addDomListener(radioButton, 'click', function() {
            autocomplete.setTypes(types);
        });
    }

    setupClickListener('changetype-all', []);
    setupClickListener('changetype-establishment', ['establishment']);
    setupClickListener('changetype-geocode', ['geocode']);
}

google.maps.event.addDomListener(window, 'load', initialize);