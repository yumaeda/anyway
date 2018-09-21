<?php

require_once('./includes/config.inc.php');
require_once(MYSQL);

$userId          = startCartSession($dbc);
$intCartType     = isset($_REQUEST['cart_type']) ? $_REQUEST['cart_type'] : 0;
$normalItemTotal = 0;
$fCapturePayment = ($intCartType != 2);

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

prepareNextQuery($dbc);

$cartContents     = generateCartContens($dbc, $userId);
$discountedAmount = 0;
$wineTotal        = $normalItemTotal;

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
        $orderId       = getOrderId();
        $coolFee       = isset($_SESSION['cool_fee']) ? $_SESSION['cool_fee'] : 0;
        $totalPayment  = floor(($wineTotal + $_SESSION['shipping_fee'] + $coolFee) * 1.08);

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

            if (empty($inputErrors))
            {
                try
                {
                    require_once(VERITRANS);

                    $objResponse = chargeWithVeritrans($orderId, $totalPayment, $cardNumber, $expMonth, $expYear, $cvc, $fCapturePayment);
                    if ($objResponse === FALSE)
                    {
                        $errorMessage = 'システムのメンテナンス中のため、ご注文を承ることができませんでした。お手数ですが、数時間後に再度お試し下さい。';
                    }
                    else if ($objResponse->status !== 'success')
                    {
                        $vResultCode = isset($objResponse->vresult_code) ? substr($objResponse->vresult_code, 0, 4) : '';
                        if ($vResultCode === '')
                        {
                            error_log($objResponse->message, 1, 'sysadm@anyway-grapes.jp');
                            $inputErrors['card_number'] = '指定されたクレジットカードでは決済できませんでした。カード番号、セキュリティーコード、有効期限を確認のうえ再入力頂くか、「銀行振り込み」を選択して下さい。';
                        }
                        else
                        {
                            error_log($vResultCode . ': ' . $objResponse->message, 1, 'sysadm@anyway-grapes.jp');

                            switch ($vResultCode)
                            {
                            case 'AG33': // カード使用不可（VISAで、CVCが違う場合このコードになった。）
                            case 'AG41': // セキュリティコード誤りです。
                            case 'AG49': // 会員番号エラー
                            case 'AG64': // 有効期限エラー
                                $inputErrors['card_number'] = '指定されたクレジットカードでは決済できませんでした。カード番号、セキュリティーコード、有効期限を確認のうえ再入力頂くか、「銀行振り込み」を選択して下さい。';
                                break;
                            case 'AB12': // DBにFCBCがありません。 
                            case 'AG39': // 取引判定保留（有人判定）です。
                            case 'AG44': // 1口座利用回数または金額オーバーです。
                            case 'AG45': // 1日限度額オーバーです。
                            case 'AG46': // クレジットカードが無効です。
                            case 'AG47': // 事故カードです。
                            case 'AG48': // 無効カードです。
                                $inputErrors['card_number'] = '指定されたカードはご利用頂けません。別のカードをご利用頂くか、カード発行会社へお問い合わせください。';
                                break;
                            case 'AC09': // ダミー取引では利用できないカード番号です。
                            case 'AC25': // カード番号パラメータの書式が誤り
                            case 'AC27': // カード番号パラメータの値がディジットエラーです。
                            case 'AC30': // カード有効期限パラメータの書式が誤り
                            case 'AC38': // パラメータで指定した金額が超過
                            case 'ACD3': // 取引は期限切れです。
                            case 'ACD4': // 元取引は成功の状態ではない
                            case 'ACD6': // 元取引が存在しません。
                            case 'AG51': // 極端に大きな金額や0円などの金額入力が誤っている。
                            case 'AG61': // お客様のカードが指定された支払い回数に対応していない。
                            case 'AG70': // 当該要求拒否です。
                            case 'AG71': // 当該自社対象業務エラーです。
                            case 'AG72': // 接続要求自社受付拒否です。
                            case 'AE10': // トランザクションが保留
                            case 'NC06': // 無効なパラメータ
                            case 'NH02': // 指定されたOrder Idの注文が既にキャンセルされているなど、取引の状態に問題がある
                            case 'NH04': // 取引が重複
                            case 'NH05': // 取引が処理中
                            case 'NH18': // 既に決済済みのOrder Idを指定してchargeを要求した場合
                            case 'NH40': // Order IDが他のサービスで使用済み
                            case 'NH42': // テストモードでのOrder IDが無効です。
                            default:
                                $errorMessage = 'システムのメンテナンス中のため、ご注文を承ることができませんでした。お手数ですが、数時間後に再度お試し下さい。';
                                break;
                            }
                        }
                    }
                }
                catch (\Exception $e)
                {
                    sendErrorMail($e);
                    $errorMessage = 'システムのメンテナンス中のため、ご注文を承ることができませんでした。お手数ですが、数時間後に再度お試し下さい。';
                }
            }
        }

        if (empty($inputErrors) && ($errorMessage === ''))
        {
            $shippingId     = $_SESSION['shipping_id'];
            $memberDiscount = (isset($_SESSION['user_id']) && isset($_SESSION['user_name'])) ? 1 : 0;

            prepareNextQuery($dbc);

            $result = mysqli_query($dbc, "CALL add_order('$orderId', $shippingId, '$cartContents', 0, $paymentMethod, $memberDiscount, $wineTotal)");
            if ($result !== FALSE)
            {
                $customerName     = mysqli_real_escape_string($dbc, $_SESSION['last_name'] . $_SESSION['first_name']);
                $customerPhonetic = mysqli_real_escape_string($dbc, $_SESSION['last_phonetic'] . $_SESSION['first_phonetic']);
                $customerEmail    = mysqli_real_escape_string($dbc, $_SESSION['email']);
                $customerAddress  = mysqli_real_escape_string($dbc, $_SESSION['post_code'] . $_SESSION['prefecture'] . $_SESSION['address']);
                $customerPhone    = mysqli_real_escape_string($dbc, $_SESSION['phone']);

                $ipAddress1 = isset($_SERVER['REMOTE_ADDR'])          ? mysqli_real_escape_string($dbc, $_SERVER['REMOTE_ADDR']) : '';
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
        include('./views/payment_new.html');
    }
    else
    {
        include('./views/emptycart.html');
    }

    mysqli_free_result($result);
}

include('./includes/footer.html');

?>
