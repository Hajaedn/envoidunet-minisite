<?php
require_once 'config.php';
include 'header.php';
?>
<html>
    <head>
        <meta charset="utf-8" />
        <title>Envoi du net</title>
    </head>
    <h2>Destination</h2>
    <body>
        <form method="POST" action="index.php">
            06700 / Saint Laurent du Var / FR
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
//var_dump($result['response']->quotes);die();


    foreach ($result['response']->quotes as $carrier) {
        $carriers->service = $carrier->service_name;
        $carriers->carrier = $carrier;
        $carriers->name = $carrier->carrier_name;
        $carriers->price = $carrier->price;
        $carriers->base_price = $carrier->base_price;
        $carriers->fuel = $carrier->fuel;
        $carriers->fuel_rate = $carrier->fuel_rate;
        $carriers->security = $carrier->security;
        $carriers->vat = $carrier->vat;
        $carriers->description = $carrier->carrier_description;
        $carriers->image = $carrier->carrier_logo
?>
<fieldset>
    <legend><img src="<?php echo $carriers->image; ?>" class="imageGauche" alt="<?php echo $carriers->name; ?>" /></legend>
    <!--        <b>Nom :  </b><a>--><?php echo $carriers->name; ?><!--</a>-->
    <b>Service : </b><a><?php echo $carriers->service; ?></a>
    <b>Prix : </b><a><?php echo $carriers->price; ?></a>
    <b>Prix de base : </b><a><?php echo $carriers->base_price; ?></a>
    <b>Fuel : </b><a><?php echo $carriers->fuel; ?></a><br>
    <?php echo $carriers->description; ?>

    <p>Choisir ce transporteur <a href="findrelays.php?pcode=06200&city=Nice&country=FR">Cliquer ici</a><br></p>
</fieldset><br>

<?php
    }
    include 'footer.php';
?>

