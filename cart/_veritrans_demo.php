<?php
use Yumaeda\Payment\Veritrans\Veritrans;
use Yumaeda\Payment\Veritrans\CreditCard;

require_once('../credentials/veritrans_defines.php');
require_once('vendor/autoload.php');

$order_id ='#' . date('YmdHis');
$total = 100;
$client_key = VERITRANS_CLIENT_KEY;
$server_key = VERITRANS_SERVER_KEY;
$card_number = '376100000000000';
$expire_month = '08';
$expire_year = '2025';
$cvv = '1234';

$veritrans = new Veritrans($client_key, $server_key);
$credit_card = new CreditCard($card_number, $expire_month, $expire_year, $cvv);

$veritrans->setCreditCard($credit_card);
$response = $veritrans->charge($order_id, $total, true);

var_dump($response);
