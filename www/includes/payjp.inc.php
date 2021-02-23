<?php

$PUBLIC_KEY = 'XXXX';
$PRIVATE_KEY = 'YYYY';

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

    $token = array();
    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        $token['error'] = array(
            'message' => curl_error($ch)
        );
    }
    else {
        $json_result = json_decode($result);
        if (!isset($json_result->error)) {
            $token['id'] = $json_result->id;
        } else {
            $token['error'] = array(
                'message' => $json_result->error->message,
                'code' => $json_result->error->code
            );
            error_log("Error Response: $result", 1, 'yumaeda@gmail.com');
        }
    }
    curl_close($ch);

    return $token;
}

function chargeWithPayjp($orderId, $totalPayment, $cardNumber, $expMonth, $expYear, $cvc, $fCapture)
{
    global $PRIVATE_KEY;

    $token = _getTokenId($cardNumber, $expMonth, $expYear, $cvc);
    if (!isset($token['id'])) {
        return json_decode(json_encode($token));
    }

    $curDirPath = dirname(__FILE__);
    require_once "$curDirPath/../includes/payjp-php/init.php";

    \Payjp\Payjp::setApiKey($PRIVATE_KEY);

    return \Payjp\Charge::create(array(
        'card' => $token['id'],
        'amount' => $totalPayment,
        'capture' => $fCapture,
        'currency' => 'jpy'
    ));
}