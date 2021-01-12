<?php

$PUBLIC_KEY = 'xxxxxxxxx';
$PRIVATE_KEY = 'yyyyyyyy';

function _getTokenId($cardNumber, $expMonth, $expYear, $cvc)
{
    global $PUBLIC_KEY;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.pay.jp/v1/tokens');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "card%5Bnumber%5D=$cardNumber&card%5Bcvc%5D=$cvc&card%5Bexp_month%5D=$expMonth&card%5Bexp_year%5D=$expYear");

    $headers = array();
    $headers[] = 'Authorization: Basic ' . base64_encode("$PUBLIC_KEY:");
    $headers[] = 'Content-Type: application/x-www-form-urlencoded';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $tokenId = '';
    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    else {
        $json_result = json_decode($result);
        $tokenId = $json_result->id;
    }
    curl_close($ch);

    return $tokenId;
}

function chargeWithPayjp($orderId, $totalPayment, $cardNumber, $expMonth, $expYear, $cvc, $fCapture)
{
    global $PRIVATE_KEY;

    $tokenId = _getTokenId($cardNumber, $expMonth, $expYear, $cvc);
    if ($tokenId !== '')
    {
        $curDirPath = dirname(__FILE__);
        require_once "$curDirPath/../includes/payjp-php/init.php";

        \Payjp\Payjp::setApiKey($PRIVATE_KEY);
        $charge = \Payjp\Charge::create(array(
            'card' => $tokenId,
            'amount' => $totalPayment,
            'captured' => $fCapture,
            'product' => $orderId,
            'currency' => 'jpy'
        ));
        return $charge; 
    }

    return null;
}