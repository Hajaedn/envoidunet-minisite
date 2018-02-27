<?php

require_once 'config.php';

$toPostcode= $_POST['toPostcode'];
$toCity= $_POST['toCity'];
$toCountry= $_POST['toCountry'];

$toPostcode= "06700";
$toCity= "Saint Laurent du Var";
$toCountry= "FR";

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
$params->to->postcode = '10115';
$params->to->city = 'Berlin';
$params->to->country = 'DE';

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


var_dump($result['response']->quotes);die();

$carriers = [];
foreach ($result['response']->quotes->item as $carrier) {
    $carriers->service = $carrier->service_name;
    $carriers->name = $carrier->carrier_name;
    $carriers->price = $carrier->price;
    $carriers->base_price = $carrier->base_price;
    $carriers->fuel = $carrier->fuel;
    $carriers->fuel_rate = $carrier->fuel_rate;
    $carriers->security = $carrier->security;
    $carriers->vat = $carrier->vat;
}

var_dump($carriers[]);die();


?>
<html>
<body>


</body>
</html>


