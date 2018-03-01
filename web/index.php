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
        </fieldset>

        <fieldset>
            <img src="assets/img/carriers/logo-copr.png" class="imageGauche" alt=" " />
            <span class="edn-info" data-relay='false' data-carrier="colisprive" data-carrier-name="Colis Privé"></span>
        </fieldset>

        <fieldset>
            <img src="http://envoidunet.com/assets/img/transporteurs/api/chronorelais.jpg" class="imageGauche" alt=" " />Chrono Relais
            <span class="edn-info" data-relay='true' data-carrier="chronorelais" data-carrier-name="Chrono Relais"></span>
            <span class="envoidunet-select-parcel" id="parcel_chronorelais">Choose a relay</span>
        </fieldset>

        <fieldset id="envoidunet-parcel-client" style='visibility: hidden'>
        </fieldset>

        <input type="hidden" id="selected_relay"/>

        <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>

        <script>

            $(document).ready(function () {

                (
                    /**
                     * Create link to findRelays when attribute 'data-relay' = 'true'
                     * @param $ jQuery
                     */
                    function($){
                        $('.envoidunet-select-carrier').each(
                            function () {
                                if(this.children('.edn-info').attr('data-relay') == 'true'){
                                    this.append('<span class="envoidunet-select-parcel" id="parcel_' + this.find('.edn-info').attr('data-carrier') + '>Choose a relay</span>');
                                }
                            }
                        )
                    }
                )(jQuery);

                var relayMap = new Envoidunet.RelayMap({
                    find_relays_url: 'findrelays.php',
                    image_dir: '/assets/img',
                    lang: {
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
                    },
                    selected_relay: function (relay) {
                        var info = "<div class='envoidunetMakerPopup'><b>" + relay.name + ' (id: ' + relay.relay_id + ')'+ '</b><br/>' +
                            relay.address1 + ', ' + relay.postcode + ' ' + relay.city;
                        info += '</div>';

                        $('#envoidunet-parcel-client').html('');
                        $('#envoidunet-parcel-client').append(info);

                        $('#envoidunet-parcel-client').css('visibility', 'visible');

                        $("#selected_relay").val(relay.relay_id);
                    },
                    debug: true
                });


                // close map if selected carrier is changed and remove parcel point selection
                $('body').delegate('input.shipping_method', 'change', function () {
                    relayMap.close_map();
                    relayMap.clear_relay();
                });

                $('body').delegate('.envoidunet-select-parcel', 'click', function () {
                    var carrier_code = $(this).attr('id').replace('parcel_', '');
                    var postcode = $('#to-postcode').text();
                    var city = $('#to-city').text();
                    var country = $('#to-country').text();
                    relayMap.show_map(carrier_code, postcode, city, country);
                });

                $('body').delegate('input.shipping_method', 'change', function () {
                    var info = $('label[for="'+$(this).attr('id')+'"] .edn-info');
                    if ($(this).is(':checked') && info.length > 0 && $(info).data('relay')) {
                        var carrier_code = $(info).data('carrier');
                        var postcode = $('#to-postcode').text();
                        var city = $('#to-city').text();
                        var country = $('#to-country').text();
                        relayMap.show_map(carrier_code, postcode, city, country);
                    }
                });

                // Show the map if a shipping method is already selected
                var checked = $('input.shipping_method:checked');
                if (checked.length > 0) {
                    var info = $('label[for="'+$(checked).attr('id')+'"] .edn-info');
                    if (info.length > 0 && $(info).data('relay')) {
                        var carrier_code = $(info).data('carrier');
                        var postcode = $('#to-postcode').text();
                        var city = $('#to-city').text();
                        var country = $('#to-country').text();
                        relayMap.show_map(carrier_code, postcode, city, country);
                    }
                }

                debugger;
            });
        </script>

        <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDLaRQHgBQDiHVKMCk_3GPM6Q1gmNQ4E-U"></script>
        <script src="assets/js/front/relays.js"></script>

    </body>
</html>