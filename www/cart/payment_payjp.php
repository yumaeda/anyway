<?php

require_once('./includes/config.inc.php');
require_once(MYSQL);

$userId = startCartSession($dbc);
$intCartType = isset($_REQUEST['cart_type']) ? $_REQUEST['cart_type'] : 0;
$normalItemTotal = 0;
$fCapturePayment = ($intCartType != 2);
$SYSTEM_ERROR = 'SYSTEM_ERROR';

// Redirect to final.php if the order is already processed.
if (isset($_SESSION['paid_order']))
{
    redirectToPage("final.php?cart_type=$intCartType");
}

// Redirect to checkout.php if the shipping information is not stored.
if (!isset($_SESSION['shipping_fee']) || 
    !isset($_SESSION['shipping_id']))
{
    redirectToPage("checkout.php?cart_type=$intCartType");
}

function generateCartContens($dbc, $userId)
{
    global $intCartType, $normalItemTotal, $discountedAmount;

    $cartContents = '';

    prepareNextQuery($dbc);

    if ($intCartType == 0)
    {
        $result = mysqli_query($dbc, "CALL get_cart_contents('$userId')");
    }
    else if ($intCartType == 1)
    {
        $result = mysqli_query($dbc, "CALL get_lucky_bag_items('$userId')");
    }
    else if ($intCartType == 2)
    {
        $result = mysqli_query($dbc, "CALL get_preorder_cart_contents('$userId')");
    }

    $normalItemTotal = 0;

    $i = 0;
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
    {
        // Store information of the items in the cart to SESSION.
        ++$i;

        $barcodeNumber = $row['barcode_number'];
        $quantity      = $row['quantity'];
        $price         = getPrice($row);
        $name          = isset($row['name']) ? $row['name'] : $row['combined_name'];
        $producer      = isset($row['producer']) ? $row['producer'] : '';

        $_SESSION['cart_item_' . $i . '_type']     = $row['type'];
        $_SESSION['cart_item_' . $i . '_name']     = $row['vintage'] . ' ' . $name;
        $_SESSION['cart_item_' . $i . '_producer'] = $producer;
        $_SESSION['cart_item_' . $i . '_id']       = $barcodeNumber;
        $_SESSION['cart_item_' . $i . '_quantity'] = $quantity;
        $_SESSION['cart_item_' . $i . '_price']    = $price;

        $normalItemTotal += ($price * $quantity);
        $cartContents .= "$barcodeNumber#$quantity;";
    }

    mysqli_free_result($result);
    $_SESSION['cart_item_count'] = $i;

    if ($intCartType == 1)
    {
        $cartContents .= "1000#$discountedAmount;";
    }

    return $cartContents;
}

function checkoutItems($dbc, $userId)
{
    global $intCartType;

    $fCheckedOut = FALSE;

    prepareNextQuery($dbc);

    if ($intCartType == 0)
    {
        mysqli_query($dbc, "CALL checkout_cart('$userId', @fSuccess)");
    }
    else if ($intCartType == 1)
    {
        mysqli_query($dbc, "CALL checkout_lucky_bag('$userId', @fSuccess)");
    }
    else if ($intCartType == 2)
    {
        mysqli_query($dbc, "CALL checkout_preorder_cart('$userId', @fSuccess)");
    }

    prepareNextQuery($dbc);
    $result = mysqli_query($dbc, 'SELECT @fSuccess');
    if (($result !== FALSE) && mysqli_num_rows($result) == 1)
    {
        list($fSuccess) = mysqli_fetch_array($result);
        if ($fSuccess == 1)
        {
            $fCheckedOut = TRUE;
        }

        mysqli_free_result($result);
    }

    return $fCheckedOut;
}

function checkinItems($dbc, $userId)
{
    global $intCartType;

    $fCheckedIn = FALSE;

    prepareNextQuery($dbc);

    if ($intCartType == 0)
    {
        mysqli_query($dbc, "CALL checkin_cart('$userId', @fSuccess)");
    }
    else if ($intCartType == 1)
    {
        mysqli_query($dbc, "CALL checkin_lucky_bag('$userId', @fSuccess)");
    }
    else if ($intCartType == 2)
    {
        mysqli_query($dbc, "CALL checkin_preorder_cart('$userId', @fSuccess)");
    }

    prepareNextQuery($dbc);
    $result = mysqli_query($dbc, 'SELECT @fSuccess');
    if (($result !== FALSE) && mysqli_num_rows($result) == 1)
    {
        list($fSuccess) = mysqli_fetch_array($result);
        if ($fSuccess == 1)
        {
            $fCheckedIn = TRUE;
        }

        mysqli_free_result($result);
    }

    return $fCheckedIn;
}

require(UTIL);
require_once('./common.php');

// Get tax rate from config file.
$config = include('./config.php');
$taxRate = $config['tax']['rate']();

prepareNextQuery($dbc);

$cartContents = generateCartContens($dbc, $userId);
$discountedAmount = 0;
$wineTotal = $normalItemTotal;

if ($intCartType == 1)
{
    $result = mysqli_query($dbc, "CALL get_lucky_bag_item_total('$userId')");
    if ($result !== FALSE)
    {
        list($originalTotal, $wineTotal) = mysqli_fetch_array($result);
        mysqli_free_result($result);

        $discountedAmount = $originalTotal - $wineTotal;
    }
}

$errorMessage = '';
$inputErrors  = array();
if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    if (checkoutItems($dbc, $userId))
    {
        $orderId = getOrderId();
        $coolFee = isset($_SESSION['cool_fee']) ? $_SESSION['cool_fee'] : 0;
        $totalPayment = floor(($wineTotal + $_SESSION['shipping_fee'] + $coolFee) * (1 + $taxRate));

        $paymentMethod = getPostValue('payment', true, $inputErrors);
        if ($paymentMethod == 1) // Compare with '==' since the post value is a string.
        {
            $cvc        = getPostValue('cvc',           true, $inputErrors);
            $holderName = getPostValue('holder_name',   true, $inputErrors);
            $expMonth   = getPostValue('expMonth',      true, $inputErrors);
            $expYear    = getPostValue('expYear',       true, $inputErrors);
            $cardNumber = getPostValue('card_number_1', true, $inputErrors) .
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

            if (empty($inputErrors)) {
                $defaultErrorMessage = 'システムのメンテナンス中のため、ご注文を承ることができませんでした。お手数ですが、数時間後に再度お試し下さい。';
                $cardError = '';
                try {
                    require_once(PAYJP);

                    $errorEmail = 'yumaeda@gmail.com';
                    $errorMessage = '';
                    $objResponse = chargeWithPayjp($orderId, $totalPayment, $cardNumber, $expMonth, $expYear, $cvc, $fCapturePayment);
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
                }
            }
        }

        if (empty($inputErrors) && ($errorMessage === ''))
        {
            $shippingId = $_SESSION['shipping_id'];
            $memberDiscount = (isset($_SESSION['user_id']) && isset($_SESSION['user_name'])) ? 1 : 0;

            prepareNextQuery($dbc);

            $result = mysqli_query($dbc, "CALL add_order('$orderId', $shippingId, '$cartContents', 0, $paymentMethod, $memberDiscount, $wineTotal)");
            if ($result !== FALSE)
            {
                $customerName = mysqli_real_escape_string($dbc, $_SESSION['last_name'] . $_SESSION['first_name']);
                $customerPhonetic = mysqli_real_escape_string($dbc, $_SESSION['last_phonetic'] . $_SESSION['first_phonetic']);
                $customerEmail = mysqli_real_escape_string($dbc, $_SESSION['email']);
                $customerAddress = mysqli_real_escape_string($dbc, $_SESSION['post_code'] . $_SESSION['prefecture'] . $_SESSION['address']);
                $customerPhone = mysqli_real_escape_string($dbc, $_SESSION['phone']);

                $ipAddress1 = isset($_SERVER['REMOTE_ADDR']) ? mysqli_real_escape_string($dbc, $_SERVER['REMOTE_ADDR']) : '';
                $ipAddress2 = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? mysqli_real_escape_string($dbc, $_SERVER['HTTP_X_FORWARDED_FOR']) : '';
                $userAgent  = $_SERVER['HTTP_USER_AGENT'];

                prepareNextQuery($dbc);

                $result = mysqli_query($dbc, "CALL set_customer_info('$orderId', '$customerName', '$customerPhonetic', '$customerEmail', '$customerAddress', '$customerPhone', '$ipAddress1', '$ipAddress2', '$userAgent')");
                if ($result !== FALSE)
                {
                    incrementOrderId();

                    $_SESSION['paid_order']      = $orderId;
                    $_SESSION['total_payment']   = $wineTotal;
                    $_SESSION['payment_method']  = $paymentMethod;
                    $_SESSION['input_validated'] = 1;

                    prepareNextQuery($dbc);

                    if ($intCartType == 0)
                    {
                        mysqli_query($dbc, "CALL clear_cart('$userId')");
                    }
                    else if ($intCartType == 1)
                    {
                        mysqli_query($dbc, "CALL clear_lucky_bag('$userId')");
                    }
                    else if ($intCartType == 2)
                    {
                        mysqli_query($dbc, "CALL clear_preorder_cart('$userId')");
                    }

                    $_SESSION['discounted_amount'] = $discountedAmount;

                    redirectToPage("final.php?cart_type=$intCartType");
                }
            }
            else
            {
                error_log("Stored Procedure Failure: add_order($orderId, $shippingId, $cartContents, 0, $paymentMethod, $wineTotal)", 1, 'sysadm@anyway-grapes.jp');
            }
        }

        checkinItems($dbc, $userId);
    }
    else
    {
        redirectToPage("cart.php?cart_type=$intCartType");
    }
}
else
{
    unset($_SESSION['payment_method']);
}

$pageTitle = '決済方法の選択｜anyway-grapes.jp';
include('./includes/header.html');

// Retrieve cart contents.
prepareNextQuery($dbc);

if ($intCartType == 0)
{
    $result = mysqli_query($dbc, "CALL get_cart_contents('$userId')");
}
else if ($intCartType == 1)
{
    $result = mysqli_query($dbc, "CALL get_lucky_bag_items('$userId')");
}
else if ($intCartType == 2)
{
    $result = mysqli_query($dbc, "CALL get_preorder_cart_contents('$userId')");
}

if ($result !== FALSE)
{
    if (mysqli_num_rows($result) > 0)
    {
        include('./views/payment_payjp.html');
    }
    else
    {
        include('./views/emptycart.html');
    }

    mysqli_free_result($result);
}

include('./includes/footer.html');

?>
