<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>EDN - findRelays demo</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <link rel="stylesheet" type="text/css" href="assets/css/front/style.css">
</head>
<body>

    <nav class="navbar navbar-dark bg-dark">
        <a class="navbar-brand mx-auto" href="#"><h4>Find relays example... by
            <img id="edn-logo" src="assets/img/logo.png" height="50px" alt=""></h4>
        </a>
    </nav>

    <div class="container">

        <form class="py-4 destination-form">
            <h3 class="mb-4">Destination form</h3>
            <div class="form-group row">
                <label for="to-country" class="col-sm-2 col-form-label">Country :</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="to-country" value="FR" placeholder="Enter a valid country code">
                </div>
            </div>
            <div class="form-group row">
                <label for="to-postcode" class="col-sm-2 col-form-label">Postcode :</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="to-postcode" value="06200" placeholder="Enter a postcode">
                </div>
            </div>
            <div class="form-group row">
                <label for="to-city" class="col-sm-2 col-form-label">City :</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="to-city" value="Saint Laurent Du Var" placeholder="Enter a city name">
                </div>
            </div>
        </form>

        <div class="row-fluid">
            <form class="py-4">
                <h3 class="mb-4">Carriers list</h3>

                <div class="btn-group btn-group-toggle" data-toggle="buttons">
                    <div class="form-group mx-1">
                        <label for="colisprive" class="btn btn-outline-secondary my-0">
                            <input name="carrier" class="carrier-selector" type="radio" value="colisprive" id="colisprive" data-relay="false" autocomplete="off">
                            Colis Privé
                            <img src="assets/img/carriers/logo-copr.png" class="carrier-img ml-3" alt=" " />
                        </label>
                    </div>

                    <div class="form-group mx-1">
                        <label for="dhl_france" class="btn btn-outline-secondary my-0">
                            <input name="carrier" class="carrier-selector" type="radio" value="dhl_france" id="dhl_france" data-relay="false" autocomplete="off">
                            DHL Domestic Express
                            <img src="assets/img/carriers/logo-dhle.png" class="carrier-img ml-3" alt=" " />
                        </label>
                    </div>

                    <div class="form-group mx-1">
                        <label for="chronorelais" class="btn btn-outline-secondary my-0">
                            <input name="carrier" class="carrier-selector" type="radio" value="chronorelais" id="chronorelais" data-relay="true" autocomplete="off">
                            Chrono Relais
                            <img src="assets/img/carriers/logo-chrp.png" class="carrier-img ml-0" alt=" " />
                        </label>
                        <small class="form-text text-muted">The only one wich triggers the relay map.</small>
                    </div>
                </div>
            </form>
        </div>

        <div class="py-4" id="selected-relay-details"></div>

        <input type="hidden" id="selected_relay"/>
    </div>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
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
                    var info = "<div><h3>Selected relay point : " + relay.name + ' (id: ' + relay.relay_id + ')'+ '</h3>' +
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