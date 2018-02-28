<?php
require_once 'config.php';
//include 'header.php';
?>
<html>
    <head>
        <meta charset="utf-8" />
        <title>Envoi du net</title>
        <link rel="stylesheet" type="text/css" href="assets/css/front/style.css">
    </head>
    <h2>Destination</h2>
    <body>
        <form method="POST" action="index.php">
            06700 / Saint Laurent du Var / FR<br>
            <img src="assets/img/carriers/logo-chrp.png" class="imageGauche" alt=" " />
            <img src="assets/img/carriers/logo-dhle.png" class="imageGauche" alt=" " />
            <img src="assets/img/carriers/logo-copr.png" class="imageGauche" alt=" " />
            <img src="assets/img/carriers/logo-fedx.png" class="imageGauche" alt=" " />
            <img src="http://envoidunet.com/assets/img/transporteurs/api/chronorelais.jpg" class="imageGauche" alt=" " />
            <input type="hidden" id="toPostcode" name="toPostcode" type="text" >
            <input type="hidden" id="toCity"     name="toCity"     type="text" >
            <input type="hidden" id="toCountry"  name="toCountry"  type="text" >
            <input type="submit"  value="Valider" />
        </form><br>
    </body>
</html>
<?php


$client = new SoapClient('http://api.envoidunet.com/?wsdl');

$login = new stdClass();
$login->api_account = $config['api_account'];
$login->api_key = $config['api_key'];


$params = new stdClass();
$params->carrier = 'fedex';


$params->from = new stdClass();
$params->from->postcode = '06700';
$params->from->city = 'Saint Laurent du Var';
$params->from->country = 'FR';

$params->to = new stdClass();
$params->to->postcode = '06700';
$params->to->city = 'Saint Laurent du var';
$params->to->country = 'FR';
//$params->to->postcode = '10115';
//$params->to->city = 'Berlin';
//$params->to->country = 'DE';

$params->weight = 1;


try {
$result = $client->getQuote($login, $params);
if ($result['error']->error_id > 0) {
$error = $result['error'];
$message = $error->error_message;
$message .= isset($error->error_description) ? ' ('.$error->error_description.')' : '';
echo $message."\n";
}

} catch (SoapFault $e) {
echo $e->getMessage()."\n";

}

?>
<h1>Liste des Transporteurs</h1>
<?php

    foreach ($result['response']->quotes as $carrier) {
//        $carriers->service = $carrier->service_name;
        $carriers->carrier = $carrier;
//        $carriers->name = $carrier->carrier_name;
//        $carriers->price = $carrier->price;
//        $carriers->base_price = $carrier->base_price;
//        $carriers->fuel = $carrier->fuel;
        $carriers->fuel_rate = $carrier->fuel_rate;
        if(isset($carrier->security)) {
            $val_security = $carrier->security;
        } else {
            $val_security = '0';
        }
        $carriers->vat = $carrier->vat;
//        $carriers->description = $carrier->carrier_description;

?>
<fieldset>
    <legend><img src="<?php echo $carrier->carrier_logo; ?>" class="imageGauche" alt="<?php echo $carrier->carrier_name; ?>" /></legend>
    <b>Carrier : </b><?php echo $carrier->carrier_name . ' (' . $carrier->carrier . ')'; ?></b>
    <b>Service : </b><a><?php echo $carrier->service_name . ' / ' . $carrier->carrier_description; ?></a><br>
    <b>Prix : </b><a><?php echo $carrier->price; ?></a>
    <b>( Prix de base : </b><a><?php echo $carrier->base_price; ?></a>
    <b> + Fuel : </b><a><?php echo $carrier->fuel; ?></a>
    <b> + Security : </b><a><?php echo $val_security; ?></a>
    <b>)</b>
    <br/>
<?php
    if($carrier->service=='relay' && $carrier->carrier!== 'kiala') {
?>
    <!--  Used to recover relays  -->
    <span class="edn-info" data-relay= 'true' data-carrier="<?php echo $carrier->carrier; ?>" data-carrier-name="<?php echo $carrier->carrier_name; ?>"></span>
    <span class="envoidunet-select-parcel" id="<?php echo 'parcel_' . $carrier->carrier; ?>">Choose a relay</span>
    <br/>
    <span>Selected : <span id="envoidunet-parcel-client"></span></span>

<?php
    }
?>
</fieldset><br>

<?php
    }
?>

<script
        src="https://code.jquery.com/jquery-3.3.1.min.js"
        integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
        crossorigin="anonymous"></script>

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
            envoidunet_show_map(carrier_code, '06200', 'Nice', 'FR');
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
