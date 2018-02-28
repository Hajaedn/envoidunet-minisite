<?php
//include 'header.php';
?>
<html>
    <head>
        <meta charset="utf-8" />
        <title>Envoi du net</title>
        <link rel="stylesheet" type="text/css" href="assets/css/front/style.css">
    </head>

    <body>

        <h1>Liste des Transporteurs</h1>
        <fieldset>
            <div>Destination :</div>
            <div>Country: <span id="to-country">FR</span></div>
            <div>City: <span id="to-city">Saint Laurent du Var</span></div>
            <div>Postal code: <span id="to-postcode">06700</span></div>
        </fieldset>

        <fieldset>
            <img src="assets/img/carriers/logo-dhle.png" class="imageGauche" alt=" " /><br>
            <span class="edn-info" data-relay='false' data-carrier="dhl_france" data-carrier-name="DHL Domestic Express"></span>
            <span class="envoidunet-select-parcel" id="parcel_dhl_france">Choose a relay</span>
        </fieldset>

        <fieldset>
            <img src="assets/img/carriers/logo-copr.png" class="imageGauche" alt=" " />
            <span class="edn-info" data-relay='false' data-carrier="colisprive" data-carrier-name="Colis Privé"></span>
            <span class="envoidunet-select-parcel" id="parcel_colisprive">Choose a relay</span>
        </fieldset>

        <fieldset>
            <img src="http://envoidunet.com/assets/img/transporteurs/api/chronorelais.jpg" class="imageGauche" alt=" " />Chrono Relais
            <span class="edn-info" data-relay='true' data-carrier="chronorelais" data-carrier-name="Chrono Relais"></span>
            <span class="envoidunet-select-parcel" id="parcel_chronorelais">Choose a relay</span>
        </fieldset>

        <fieldset id="envoidunet-parcel-client" style='visibility: hidden'>
        </fieldset>

        <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>

        <script>
            var envoidunet_lang = {
                'relayName' : {
                    'mondialrelay' :'Mondial Relay',
                    'dpd_relay' :'DPD Relay',
                    'chronorelais' : 'Chronopost Relais',
                    'kiala' :'Kiala',
                    'ups_express_saver_relay' : 'Ups Express Saver Relay',
                    'ups_standard_mono_relay' : 'Ups Standard Monocolis Relay',
                    'ups_standard_multi_relay' : 'Ups Standard Multicolis Relay',
                    'colissimo_relais' : 'Colissimo Relais'
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

            var carrier_code = '';
            var postcode = $('#to-postcode').text();
            var city = $('#to-city').text();
            var country = $('#to-country').text();


            var envoidunet_parcels = null;
            var infowindow = null;
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

            $(document).ready(function () {

                // see function load_relay_js in class envoidunet
                $('body').append(envoidunet_map_container);

                // close map if selected carrier is changed and remove parcel point selection
                $('body').delegate('input.shipping_method', 'change', function () {
                    envoidunet_close_map();
                    $('input[name="_envoidunet_relay"]').remove();
                });

                $('body').delegate('.envoidunet-select-parcel', 'click', function () {
                    carrier_code = $(this).attr('id').replace('parcel_', '');
                    envoidunet_show_map(carrier_code, postcode, city, country);
                });

                $('body').delegate('input.shipping_method', 'change', function () {
                    var info = $('label[for="'+$(this).attr('id')+'"] .edn-info');
                    if ($(this).is(':checked') && info.length > 0 && $(info).data('relay')) {
                        carrier_code = $(info).data('carrier');
                        envoidunet_show_map(carrier_code, postcode, city, country);
                    }
                });

                // Show the map if a shipping method is already selected
                var checked = $('input.shipping_method:checked');
                if (checked.length > 0) {
                    var info = $('label[for="'+$(checked).attr('id')+'"] .edn-info');
                    if (info.length > 0 && $(info).data('relay')) {
                        carrier_code = $(info).data('carrier');
                        envoidunet_show_map(carrier_code, postcode, city, country);
                    }
                }

                $('#envoidunetMap').delegate('.parcelButton', 'click', function (e) {
                    e.preventDefault();
                    envoidunet_select_relay($(this).attr("data"));
                });

                $('.envoidunetClose').click(envoidunet_close_map);
            });
        </script>

        <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDLaRQHgBQDiHVKMCk_3GPM6Q1gmNQ4E-U"></script>
        <script src="assets/js/front/relays.js"></script>

    </body>
</html>