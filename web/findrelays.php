<?php

require_once 'config.php';

$client = new SoapClient('http://api.envoidunet.com/?wsdl');

$login = new stdClass();
$login->api_account = $config['api_account'];
$login->api_key = $config['api_key'];

$params = new stdClass();
$params->carrier = 'dpd_relay';
$params->postcode = '06700';
$params->city = 'Saint Laurent du Var';
$params->country = 'FR';

try {
    $result = $client->findRelays($login, $params);
    if ($result['error']->error_id > 0) {
        $error = $result['error'];
        $message = $error->error_message;
        $message .= isset($error->error_description) ? ' ('.$error->error_description.')' : '';
        echo $message."\n";
    }

    $relays = isset($result['response']->relays) ? $result['response']->relays : [];

    echo json_encode($relays, JSON_PRETTY_PRINT);

} catch (SoapFault $e) {
    echo $e->getMessage()."\n";
}

