<?php

require_once('./includes/config.inc.php');

$errorMessage = '';
$inputErrors  = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $email = getPostValue('email', true, $inputErrors);
    $amount = getPostValue('amount', true, $inputErrors);
    $orderId = getPostValue('order_id', true, $inputErrors);
    $cvc = getPostValue('cvc', true, $inputErrors);
    $holderName = getPostValue('holder_name', true, $inputErrors);
    $expMonth = getPostValue('expMonth', true, $inputErrors);
    $expYear = getPostValue('expYear', true, $inputErrors);
    $cardNumber =
        getPostValue('card_number_1', true, $inputErrors) .
        getPostValue('card_number_2', true, $inputErrors) .
        getPostValue('card_number_3', true, $inputErrors) .
        getPostValue('card_number_4', true, $inputErrors);

    if (!preg_match("/^[0-9]{3,4}$/", $cvc))
    {
        $inputErrors['cvc'] = '３桁もしくは４桁の半角数字を入力してください。';
    }

    if (!preg_match("/^4[0-9]{12,15}$/",         $cardNumber) && // VISA
        !preg_match("/^5[1-5][0-9]{14}$/",       $cardNumber) && // MasterCard
        !preg_match("/^35[2-8][0-9][0-9]{12}$/", $cardNumber) && // JCB
        !preg_match("/^3[47][0-9]{13}$/",        $cardNumber) && // American Express
        !preg_match("/^3[0-9]{13}$/",            $cardNumber))   // Diners Club International
    {
        $inputErrors['card_number'] = '再度確認の上、正しいカード番号を入力してください。';
    }

    if (empty($inputErrors))
    {
        $defaultErrorMessage = 'システムのメンテナンス中のため、ご注文を承ることができませんでした。お手数ですが、数時間後に再度お試し下さい。';
        $cardError = '';
        try {
            require_once(PAYJP);

            $errorEmail = 'yumaeda@gmail.com';
            $errorMessage = '';
            $objResponse = chargeWithPayjp($orderId, $amount, $cardNumber, $expMonth, $expYear, $cvc, true);
            if ($objResponse === null) {
                $errorMessage = $defaultErrorMessage;
            } else {
                if ($objResponse->error) {
                    if (isset($objResponse->error->code)) {
                        $cardError = convertPayjpErrorCodeToText($objResponse->error->code);
                    }
                } else if (!$objResponse->paid) {
                    $vResultCode = $objResponse->failure_code;
                    if ($vResultCode === '') {
                        $cardError = '指定されたクレジットカードでは決済できませんでした。カード番号、セキュリティーコード、有効期限を確認のうえ再入力頂くか、「銀行振り込み」を選択して下さい。';
                    } else {
                        $cardError = convertPayjpErrorCodeToText($vResultCode);
                    }
                }
            }
        } catch (\Exception $e) {
            if (isset($e->jsonBody) && isset($e->jsonBody['error']) && isset($e->jsonBody['error']['code'])) {
                $cardError = convertPayjpErrorCodeToText($e->jsonBody['error']['code']);
            } else {
                sendDebugMail($e);
                $cardError = $SYSTEM_ERROR;
            }
        }

        if ($cardError !== '') {
            if ($cardError === $SYSTEM_ERROR) {
                $errorMessage = $defaultErrorMessage;
                $cardError = '';
            } else {
                $inputErrors['card_number'] = $cardError;
            }
        } else {
            $subject = 'Anyway-Grapes: お支払金額';
            $orderEmail = 'order@anyway-grapes.jp';
            $archiveEmail = 'archive@anyway-grapes.jp';

            require_once('./mails/text/airlink_payment_confirmed_mail_body.php');
            require(E_MAIL);
            sendMailAsPlainText(
                $email,
                $holderName,
                $orderEmail,
                $archiveEmail,
                $subject,
                $textMessage
            );
            redirectToPage('paid.php');
        }
    }
}

$pageTitle = 'クレジットカード決済｜anyway-grapes.jp';
include('./includes/header.html');
include('./views/pay.html');
include('./includes/footer.html');

?>
