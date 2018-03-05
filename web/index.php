<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>EDN - findRelays demo</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/css/bootstrap.min.css" integrity="sha384-Zug+QiDoJOrZ5t4lssLdxGhVrurbmBWopoEl+M6BdEfwnCJZtKxi1KgxUyJq13dy" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="assets/css/front/style.css">
</head>
<body>

    <nav class="navbar navbar-light bg-light">
        <a class="navbar-brand" href="#">
            <img src="assets/img/logo.png" height="50px" alt="">
        </a>

        <span class="navbar-text">Find relays demo</span>

    </nav>

    <div class="container">

        <h2>Destination</h2>
        <form class="destination-form">
            <div class="form-group row">
                <label for="to-country" class="col-sm-2 col-form-label">Country :</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="to-country" value="FR">
                </div>
            </div>
            <div class="form-group row">
                <label for="to-postcode" class="col-sm-2 col-form-label">Postcode :</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="to-postcode" value="06200">
                </div>
            </div>
            <div class="form-group row">
                <label for="to-city" class="col-sm-2 col-form-label">City :</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="to-city" value="Saint Laurent Du Var">
                </div>
            </div>
        </form>

        <h2>Carriers list</h2>
        <form>
            <div class="form-row vertical-align">
                <div class="col-3">
                    <label for="dhl_france"><input name="carrier" class="carrier-selector" type="radio" value="dhl_france" id="dhl_france" data-relay="false"> DHL Domestic Express</label>
                </div>
                <div class="col-3">
                    <label for="dhl_france">
                        <img src="assets/img/carriers/logo-dhle.png" class="imageGauche" alt=" " />
                    </label>
                </div>
            </div>

            <div class="form-row vertical-align">
                <div class="col-3">
                    <label for="colisprive"><input name="carrier" class="carrier-selector" type="radio" value="colisprive" id="colisprive" data-relay="false"> Colis Privé</label>
                </div>
                <div class="col-3">
                    <label for="colisprive">
                        <img src="assets/img/carriers/logo-copr.png" class="imageGauche" alt=" " />
                    </label>
                </div>
            </div>

            <div class="form-row vertical-align">
                <div class="col-3">
                    <label for="chronorelais"><input name="carrier" class="carrier-selector" type="radio" value="chronorelais" id="chronorelais" data-relay="true"> Chrono Relais</label>
                </div>
                <div class="col-3">
                    <label for="chronorelais">
                        <img src="assets/img/carriers/logo-chrp.png" class="imageGauche" alt=" " />
                    </label>
                </div>
            </div>
        </form>

        <div id="selected-relay-details"></div>

        <input type="hidden" id="selected_relay"/>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/js/bootstrap.min.js" integrity="sha384-a5N7Y/aK3qNeh15eJKGWxsqtnX/wWdSZSKp+81YjTmS15nvnvxKHuzaWwXHDli+4" crossorigin="anonymous"></script>

    <script>

        $(document).ready(function () {

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
                    'day_7' : 'dimanche',
                    'Unable to load relay points' : 'Impossible de charger les points relais',
                    'No relaypoint available' : 'Aucun point relais disponible'
                },
                selected_relay: function (relay) {
                    var info = "<div class='envoidunetMakerPopup'><b>" + relay.name + ' (id: ' + relay.relay_id + ')'+ '</b><br/>' +
                        relay.address1 + ', ' + relay.postcode + ' ' + relay.city;
                    info += '</div>';

                    $('#selected-relay-details').html('');
                    $('#selected-relay-details').append(info);

                    $('#selected-relay-details').css('visibility', 'visible');

                    $("#selected_relay").val(relay.relay_id);
                },
                debug: true
            });


            // close map if selected carrier is changed and remove parcel point selection
            $('body').on('change', 'input.carrier-selector' , function () {
                relayMap.close_map();
                relayMap.clear_relay();
            });

            // Performs find relays request by selecting a carrier with data-relay="true"
            $('body').on('click', 'input.carrier-selector[data-relay="true"]', function () {
                var carrier_code = $(this).attr('id');
                var postcode = $('#to-postcode').val();
                var city = $('#to-city').val();
                var country = $('#to-country').val();
                relayMap.show_map(carrier_code, postcode, city, country);
            });

            $('body').on('change', 'input.carrier-selector[data-relay="true"]', function () {
                var carrier_code = $(this).attr('id');
                var postcode = $('#to-postcode').val();
                var city = $('#to-city').val();
                var country = $('#to-country').val();
                relayMap.show_map(carrier_code, postcode, city, country);
            });

            // Show the map if a shipping method is already selected
            var checked = $('input.carrier-selector:checked[data-relay="true"]');
            if (checked.length > 0) {
                var carrier_code = $(this).attr('id');
                var postcode = $('#to-postcode').val();
                var city = $('#to-city').val();
                var country = $('#to-country').val();
                relayMap.show_map(carrier_code, postcode, city, country);
            }

        });
    </script>

    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDLaRQHgBQDiHVKMCk_3GPM6Q1gmNQ4E-U"></script>
    <script src="assets/js/front/relays.js"></script>

</body>
</html>