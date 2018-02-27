<?php

?>
<head>
    <link rel="stylesheet" type="text/css" href="assets/css/front/style.css">
</head>
<body>



<!-- For each carrier -->

<div>
    <span>DPD Relay</span>
    <br/>
    <span class="edn-info" data-relay="true" data-carrier="dpd_relay" data-carrier-name="DPD Relay"></span>
    <span class="envoidunet-select-parcel" id="parcel_dpd_relay">Choose a relay</span>
    <br/>
    <span>Selected : <span id="envoidunet-parcel-client"></span></span>
</div>


<script
    src="https://code.jquery.com/jquery-3.3.1.min.js"
    integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
    crossorigin="anonymous"></script>

<script>
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
</script>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDLaRQHgBQDiHVKMCk_3GPM6Q1gmNQ4E-U"></script>
<script src="assets/js/front/relays.js"></script>

</body>