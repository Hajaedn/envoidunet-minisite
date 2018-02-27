(function($) {

    var envoidunet_lang = {
        'relayName' : {
            'mondialrelay' :'Mondial Relay',
            'dpd_relay' :'DPD Relay'
        },
        'Opening hours' : 'Heures d\'ouverture',
        'day_1' : 'lundi',
        'day_2' : 'mardi',
        'day_3' : 'mercredi',
        'day_4' : 'jeudi',
        'day_5' : 'vendredi',
        'day_6' : 'samedi',
        'day_7' : 'dimanche'
    };

    var envoidunet_parcels = null;
    var infowindow = null;
    var carrier_code = '';
    var map = null;
    var bounds = null;
    var markers = [];
    var parcels_info = [];
    var envoidunet_plugin_url = '';

    var envoidunet_map_container = '<div id="envoidunetMap">\n' +
        '    <div id="envoidunetMapInner">\n' +
        '        <div class="envoidunetClose" title="Close map"></div>\n' +
        '        <div id="envoidunetMapContainer">\n' +
        '            <div id="envoidunetMapCanvas"></div>\n' +
        '        </div>\n' +
        '        <div id="envoidunetPrContainer"></div>\n' +
        '    </div>\n' +
        '</div>\n';

    var envoidunet_ajaxurl = 'findrelays.php';

    $(window).load(function () {

        // see function load_relay_js in class envoidunet
        $('body').append(envoidunet_map_container);

        // close map if selected carrier is changed and remove parcel point selection
        $('body').delegate('input.shipping_method', 'change', function () {
            envoidunet_close_map();
            $('input[name="_envoidunet_relay"]').remove();
        });

        $('body').delegate('.envoidunet-select-parcel', 'click', function () {
            carrier_code = $(this).attr('id').replace('parcel_', '');
            envoidunet_show_map(carrier_code);
        });

        $('body').delegate('input.shipping_method', 'change', function () {
            var info = $('label[for="'+$(this).attr('id')+'"] .edn-info');
            if ($(this).is(':checked') && info.length > 0 && $(info).data('relay')) {
                carrier_code = $(info).data('carrier');
                envoidunet_show_map(carrier_code);
            }
        });

        // Show the map if a shipping method is already selected
        var checked = $('input.shipping_method:checked');
        if (checked.length > 0) {
            var info = $('label[for="'+$(checked).attr('id')+'"] .edn-info');
            if (info.length > 0 && $(info).data('relay')) {
                carrier_code = $(info).data('carrier');
                envoidunet_show_map(carrier_code);
            }
        }

        $('#envoidunetMap').delegate('.parcelButton', 'click', function (e) {
            e.preventDefault();
            envoidunet_select_relay($(this).attr("data"));
        });

        $('.envoidunetClose').click(envoidunet_close_map);
    });

    /*
     * Initialize the google map for a new display
     */
    function envoidunet_init_map() {
        $('#envoidunetMap').css('display', 'block');
        // set offset on the middle of the page (or top of the page for small screens)
        var offset = $(window).scrollTop() + ($(window).height() - $('#envoidunetMap').height()) / 2;
        if (offset < $(window).scrollTop()) {
            offset = $(window).scrollTop();
        }
        $('#envoidunetMap').css('top', offset + 'px');
        var options = {
            zoom: 11,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };

        if (!map && $('#envoidunetMapCanvas').length > 0) {
            map = new google.maps.Map(document.getElementById("envoidunetMapCanvas"), options);
        }
        bounds = new google.maps.LatLngBounds();

        infowindow = new google.maps.InfoWindow();
        google.maps.event.trigger(map, 'resize');
    }

    function envoidunet_show_map(carrier_code) {
        envoidunet_init_map();
        $.ajax({
            url: envoidunet_ajaxurl,
            data: {action: 'envoidunet_find_relays', carrier_code: carrier_code},
            dataType: 'json',
            timeout: 15000,
            error: envoidunet_error_relays,
            success: envoidunet_show_relays
        });
        if (typeof $(this).attr("shown") == "undefined") {
            google.maps.event.trigger(map, 'resize');
        }
    }

    /*
     * Close and clear the google map
     */
    function envoidunet_close_map() {
        $('#envoidunetMap').css('display', 'none');
        $('#envoidunetPrContainer').html('');
        for (var i = 0; i < markers.length; i++) {
            markers[i].setMap(null);
        }
        markers = [];
        parcels_info = [];
    }

    /*
     * We update the zoom level to the best fit
     */
    function envoidunet_update_zoom_map() {
        // zoom only if we have all markers
        if (envoidunet_parcels.length === 0 || (envoidunet_parcels.length !== 0 && markers.length < envoidunet_parcels.length)) {
            return;
        }
        var bounds = new google.maps.LatLngBounds();

        for (var i = 0; i < markers.length; i++) {
            if (typeof markers[i] != 'undefined') {
                bounds.extend(markers[i].getPosition());
            }
        }
        map.setCenter(bounds.getCenter());
        google.maps.event.addDomListener(window, 'resize', function () {
            map.setCenter(bounds.getCenter());
        });
        map.fitBounds(bounds);
        map.setZoom(map.getZoom() - 1);
        // if only 1 marker, unzoom
        if (map.getZoom() > 15) {
            map.setZoom(15);
        }
        google.maps.event.trigger(map, 'resize');
    }

    /*
     * Add google map events on a marker
     */
    function envoidunet_add_google_map_events(marker, code) {
        // open popup on map click
        google.maps.event.addListener(marker, "click", function () {
            if (typeof openedWindow != 'undefined' && openedWindow !== null) {
                openedWindow.close();
            }
            openedWindow = infowindow;
            infowindow.setContent(this.get("content"));
            infowindow.open(map, this);
        });
        // open popup on right column title click
        $(document).delegate(".showInfo" + code, "click", function () {
            if (typeof openedWindow != 'undefined' && openedWindow !== null) {
                openedWindow.close();
            }
            openedWindow = infowindow;
            infowindow.setContent(marker.get("content"));
            infowindow.open(map, marker);
        });
    }

    /*
     * Now that we have all the parcel points, we display them
     */
    function envoidunet_show_relays(relays) {
        envoidunet_parcels = relays;

        if (relays.length === 0) {
            alert(envoidunet_lang.noPP);
            return;
        }

        var choose = "";
        // get "choose this relay point" translation
        if (relays[0].carrier && envoidunet_lang.relayName[relays[0].carrier]) {
            choose = envoidunet_lang.relayName[relays[0].carrier];
        } else {
            choose = envoidunet_lang.relayName.default;
        }

        // add parcel point markers
        for (var i in relays) {
            // prevents bizarre javascript bug when array has extra prototype function
            if (isNaN(parseInt(i)) || i >= 10) {
                continue;
            }

            var point = relays[i];
            var address = point.address1;
            var city = point.city;
            var postcode = point.postcode;
            var name = point.name;
            var carrier = point.carrier;
            var info = "<div class='envoidunetMakerPopup'><b>" + name + '</b><br/>' +
                '<a href="#" class="parcelButton envoidunetPointer" data="' + point.relay_id + '">' + choose + '</a><br/>' +
                address + ', ' + postcode + ' ' + city + '<br/>' + "<b>" + envoidunet_lang['Opening hours'] +
                "</b><br/>" + '<div class="envoidunetSchedule">';

            for (var j in point.openingHours) {
                var day = point.openingHours[j];

                info += '<span class="envoidunetDay">' + envoidunet_lang['day_' + day.day] + '</span>';
                info += day.hours;
                info += '<br/>';
            }
            info += '</div>';

            parcels_info[i] = info;

            var envoidunetMarker = {
                url: envoidunet_plugin_url + "/assets/img/marker-number-" + (parseInt(i) + 1) + ".png",
                origin: new google.maps.Point(0, 0),
                anchor: new google.maps.Point(13, 37),
                scaledSize: new google.maps.Size(26, 37)
            };

            var latlng = new google.maps.LatLng(parseFloat(point.latitude), parseFloat(point.longitude));

            var marker = new google.maps.Marker({
                map: map,
                position: latlng,
                icon: envoidunetMarker,
                title: name
            });
            marker.set("content", parcels_info[i]);
            bounds.extend(marker.getPosition());

            envoidunet_add_google_map_events(marker, carrier);

            markers[i] = marker;
            // update zoom
            envoidunet_update_zoom_map();
            //})(i);
        }

        map.fitBounds(bounds);

        // add list of points html (in map pop)
        var html = '';
        html += '<table><tbody>';
        for (i in relays) {
            // prevents bizarre javascript bug when array has extra prototype function
            if (isNaN(parseInt(i)) || i >= 10) {
                continue;
            }

            point = relays[i];
            html += '<tr>';
            html += '<td><img src="' + envoidunet_plugin_url + '/assets/img/marker-number-' + (parseInt(i) + 1) + '.png" class="envoidunetMarker" />';
            html += '<div class="envoidunetPointTitle"><a class="showInfo' + point.carrier + ' envoidunetPointer">' + point.name + '</a></div><br/>';
            html += point.address1 + '<br/>';
            html += point.postcode + ' ' + point.city + '<br/>';
            html += '<a class="parcelButton envoidunetPointer" data="' + point.relay_id + '"><b>' + choose + '</b></a>';
            html += '</td>';
            html += '</tr>';
        }
        html += '</tbody></table>';
        $('#envoidunetMap #envoidunetPrContainer').html(html);


        // remove info if we click on the map
        google.maps.event.addListener(map, "click", function () {
            infowindow.close();
        });
    }

    /*
     * We clicked on the "choose this relay point" link on the map popup
     */
    function envoidunet_select_relay(pointCode) {
        var point;
        $.each(envoidunet_parcels, function (index, el) {
            if (el.relay_id == pointCode) {
                point = el;
            }
        });

        // add input if not present (or form has been refreshed)
        if ($('input[name="_envoidunet_relay"]').length === 0) {
            $('form[name="checkout"]').append($('<input type="hidden" name="_envoidunet_relay" />'));
        }
        $('input[name="_envoidunet_relay"]').val(pointCode);
        $('#envoidunet-parcel-client').html(point.name);
        $('#envoidunetMap').css('display', 'none');
        envoidunet_close_map();
    }

    function envoidunet_error_relays(jqXHR, textStatus, errorThrown) {
        alert(envoidunet_lang['Unable to load relay points'] + ' : ' + errorThrown);
    }

    /* from 12:00:00 to 12:00 */
    function envoidunet_formatHours(time) {
        var explode = time.split(':');
        if (explode.length == 3) {
            time = explode[0] + ':' + explode[1];
        }
        return time;
    }
})(jQuery);