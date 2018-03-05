if (typeof Envoidunet === 'undefined') {

    var Envoidunet = {};

    Envoidunet.RelayMap = (function ($) {

        var map_container = '<div id="envoidunetMap">\n' +
            '    <div id="envoidunetMapInner">\n' +
            '        <div class="envoidunetClose" title="Close map"></div>\n' +
            '        <div id="envoidunetMapContainer">\n' +
            '            <div id="envoidunetMapCanvas"></div>\n' +
            '        </div>\n' +
            '        <div id="envoidunetPrContainer"></div>\n' +
            '    </div>\n' +
            '</div>\n';

        var parcels = null;
        var infowindow = null;
        var map = null;
        var bounds = null;
        var markers = [];
        var parcels_info = [];

        var defaultOptions = {
            lang: {
                'relayName': {
                    'mondialrelay': 'Mondial Relay',
                    'dpd_relay': 'DPD Relay',
                    'chronorelais': 'Chronopost Relais',
                    'kiala': 'Kiala',
                    'ups_express_saver_relay': 'Ups Express Saver Relay',
                    'ups_standard_mono_relay': 'Ups Standard Monocolis Relay',
                    'ups_standard_multi_relay': 'Ups Standard Multicolis Relay',
                    'colissimo_relais': 'Colissimo Relais'
                },
                'Opening hours': 'Heures d\'ouverture',
                'day_1': 'lundi',
                'day_2': 'mardi',
                'day_3': 'mercredi',
                'day_4': 'jeudi',
                'day_5': 'vendredi',
                'day_6': 'samedi',
                'day_7': 'dimanche'
            },
            debug: false
        };

        var relayMap = function (options) {

            this.options = $.extend(defaultOptions, options);

            this.selected_relay = null;

            // see function load_relay_js in class envoidunet
            this.el = $(map_container);

            var self = this;

            this.el.delegate('.parcelButton', 'click', function (e) {
                e.preventDefault();
                var relay = $(this).attr("data");
                self.select_relay(relay);
            });

            this.el.find('.envoidunetClose').click(this.close_map.bind(this));

            $('body').append(this.el);
        };

        /*
         * Initialize the google map for a new display
         */
        relayMap.prototype.init_map = function () {
            this.el.css('display', 'block');
            // set offset on the middle of the page (or top of the page for small screens)
            var offset = $(window).scrollTop() + (window.innerHeight - this.el.height()) / 2;
            if (offset < $(window).scrollTop()) {
                offset = $(window).scrollTop();
            }

            this.el.css('top', offset + 'px');
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
        };

        relayMap.prototype.show_map = function (carrier_code, postcode, city, country) {
            this.init_map();
            $.ajax({
                url: this.options.find_relays_url,
                data: {carrier_code: carrier_code, postcode: postcode, city: city, country: country},
                dataType: 'json',
                timeout: 15000,
                error: this.error_relays.bind(this),
                success: this.show_relays.bind(this)
            });
            if (typeof $(this).attr("shown") == "undefined") {
                google.maps.event.trigger(map, 'resize');
            }
        };

        /*
         * Close and clear the google map
         */
        relayMap.prototype.close_map = function () {
            this.el.css('display', 'none');
            this.el.find('#envoidunetPrContainer').html('');
            for (var i = 0; i < markers.length; i++) {
                markers[i].setMap(null);
            }
            markers = [];
            parcels_info = [];
        };

        /*
         * We update the zoom level to the best fit
         */
        relayMap.prototype.update_zoom_map = function () {
            // zoom only if we have all markers
            if (parcels.length === 0 || (parcels.length !== 0 && markers.length < parcels.length)) {
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
        };

        /*
         * Add google map events on a marker
         */
        relayMap.prototype.add_google_map_events = function (marker, code) {
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
            this.el.delegate(".showInfo" + code, "click", function () {
                if (typeof openedWindow != 'undefined' && openedWindow !== null) {
                    openedWindow.close();
                }
                openedWindow = infowindow;
                infowindow.setContent(marker.get("content"));
                infowindow.open(map, marker);
            });
        };

        /*
         * Now that we have all the parcel points, we display them
         */
        relayMap.prototype.show_relays = function (response) {
            var relays = response.relays;
            var error_msg = response.error !== '' ? (' : ' + response.error) : '';
            parcels = relays;

            if (relays.length === 0) {
                if (this.options.debug) {
                    console.debug(this.options.lang['No relaypoint available'] + error_msg);
                }
                alert(this.options.lang['No relaypoint available'] + error_msg);
                return;
            }

            var choose = "";
            // get "choose this relay point" translation
            if (relays[0].carrier && this.options.lang.relayName[relays[0].carrier]) {
                choose = this.options.lang.relayName[relays[0].carrier];
            } else {
                choose = this.options.lang.relayName.default;
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
                    address + ', ' + postcode + ' ' + city + '<br/>' + "<b>" + this.options.lang['Opening hours'] +
                    "</b><br/>" + '<div class="envoidunetSchedule">';

                for (var j in point.openingHours) {
                    var day = point.openingHours[j];

                    info += '<span class="envoidunetDay">' + this.options.lang['day_' + day.day] + '</span>';
                    info += day.hours;
                    info += '<br/>';
                }
                info += '</div>';

                parcels_info[i] = info;

                var envoidunetMarker = {
                    url: this.options.image_dir + "/marker-number-" + (parseInt(i) + 1) + ".png",
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

                this.add_google_map_events(marker, carrier);

                markers[i] = marker;
                // update zoom
                this.update_zoom_map();
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
                html += '<td><img src="' + this.options.image_dir + '/marker-number-' + (parseInt(i) + 1) + '.png" class="envoidunetMarker" />';
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
        };

        /*
         * We clicked on the "choose this relay point" link on the map popup
         */
        relayMap.prototype.select_relay = function (pointCode) {

            if (this.options.debug) {
                console.debug("Selected relay : ", pointCode);
            }

            var point;
            $.each(parcels, function (index, el) {
                if (el.relay_id == pointCode) {
                    point = el;
                }
            });

            this.selected_relay = point;
            this.options.selected_relay(point);

            $('#envoidunetMap').css('display', 'none');
            this.close_map();
        };

        relayMap.prototype.error_relays = function (jqXHR, textStatus, errorThrown) {
            if (this.options.debug) {
                console.debug(this.options.lang['Unable to load relay points'] + ' : ' + errorThrown);
            }
            alert(this.options.lang['Unable to load relay points'] + ' : ' + errorThrown);
        };

        /* from 12:00:00 to 12:00 */
        relayMap.prototype.formatHours = function (time) {
            var explode = time.split(':');
            if (explode.length == 3) {
                time = explode[0] + ':' + explode[1];
            }
            return time;
        };

        relayMap.prototype.clear_relay = function () {
            this.selected_relay = null;
        };

        relayMap.prototype.get_selected_relay = function () {
            return this.selected_relay;
        };

        return relayMap;

    })(jQuery);
}