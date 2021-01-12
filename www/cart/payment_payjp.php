<?php

require_once('./includes/config.inc.php');
require_once(MYSQL);

$userId = startCartSession($dbc);
$intCartType = isset($_REQUEST['cart_type']) ? $_REQUEST['cart_type'] : 0;
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
    // if (checkoutItems($dbc, $userId))
    // {
        $orderId = getOrderId();
        $coolFee = isset($_SESSION['cool_fee']) ? $_SESSION['cool_fee'] : 0;
        $totalPayment  = floor(($wineTotal + $_SESSION['shipping_fee'] + $coolFee) * (1 + $taxRate));

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
                    require_once(PAYJP);

                    $objResponse = chargeWithPayjp($orderId, $totalPayment, $cardNumber, $expMonth, $expYear, $cvc, $fCapturePayment);
                    var_dump($objResponse);
                    if ($objResponse === null)
                    {
                        $errorMessage = 'システムのメンテナンス中のため、ご注文を承ることができませんでした。お手数ですが、数時間後に再度お試し下さい。';
                    }
                    else if (!$objResponse->paid)
                    {
                        $vResultCode = $objResponse->failure_code;
                        if ($vResultCode === '')
                        {
                            echo $objResponse->failure_message;
                            // error_log($objResponse->failure_message, 1, 'sysadm@anyway-grapes.jp');
                            $inputErrors['card_number'] = '指定されたクレジットカードでは決済できませんでした。カード番号、セキュリティーコード、有効期限を確認のうえ再入力頂くか、「銀行振り込み」を選択して下さい。';
                        }
                        else
                        {
                            echo $objResponse->failure_message;
                            // error_log($vResultCode . ': ' . $objResponse->failure_message, 1, 'sysadm@anyway-grapes.jp');
                            switch ($vResultCode)
                            {
                                case 'incorrect_card_data': // いずれかのカード情報が誤っている
                                case 'invalid_expiry_month': // 不正な有効期限月
                                case 'invalid_expiry_year': // 不正な有効期限年
                                    $inputErrors['card_number'] = '指定されたクレジットカードでは決済できませんでした。カード番号、セキュリティーコード、有効期限を確認のうえ再入力頂くか、「銀行振り込み」を選択して下さい。';
                                    break;
                                case 'expired_card': // 有効期限切れ
                                case 'card_declined': // カード会社によって拒否されたカード
                                    $inputErrors['card_number'] = '指定されたカードはご利用頂けません。別のカードをご利用頂くか、カード発行会社へお問い合わせください。';
                                    break;
                                case 'processing_error': // 決済ネットワーク上で生じたエラー
                                case 'unacceptable_brand': // 対象のカードブランドが許可されていない
                                case 'invalid_id': // 不正なID
                                case 'no_api_key': // APIキーがセットされていない
                                case 'invalid_api_key': // 不正なAPIキー
                                case 'invalid_expiry_days': // 不正な失効日数
                                case 'unnecessary_expiry_days': // 失効日数が不要なパラメーターである場合
                                case 'invalid_flexible_id': // 不正なID指定
                                case 'invalid_string_length': // 不正な文字列長
                                case 'invalid_country': // 不正な国名コード
                                case 'invalid_currency': // 不正な通貨コード
                                case 'invalid_amount': // 不正な支払い金額
                                case 'invalid_card': // 不正なカード
                                case 'invalid_boolean': // 不正な論理値
                                case 'no_allowed_param': // パラメーターが許可されていない場合
                                case 'no_param': // パラメーターが何もセットされていない
                                case 'invalid_querystring': // 不正なクエリー文字列
                                case 'missing_param': // 必要なパラメーターがセットされていない
                                case 'invalid_param_key': // 指定できない不正なパラメーターがある
                                case 'failed_payment': // 指定した支払いが失敗している場合
                                case 'invalid_amount_to_not_captured': // 確定されていない支払いに対して部分返金ができない
                                case 'capture_amount_gt_net': // 支払い確定額が元の支払い額より大きい
                                case 'already_captured': // すでに支払いが確定済み
                                case 'cant_capture_refunded_charge': // 返金済みの支払いに対して支払い確定はできない
                                case 'cant_reauth_refunded_charge': // 返金済みの支払いに対して再認証はできない
                                case 'charge_expired': // 認証が失効している支払い
                                case 'already_exist_id': // すでに存在しているID
                                case 'token_already_used': // すでに使用済みのトークン
                                case 'invalid_billing_day': // 不正な支払い実行日
                                case 'too_many_metadata_keys': // metadataキーの登録上限(20)を超過している
                                case 'invalid_metadata_key': // 不正なmetadataキー
                                case 'invalid_metadata_value': // 不正なmetadataバリュー
                                case 'test_card_on_livemode': // 本番モードのリクエストにテストカードが使用されている
                                case 'not_activated_account': // 本番モードが許可されていないアカウント
                                case 'too_many_test_request': // テストモードのリクエストリミットを超過している
                                case 'payjp_wrong': // PAY.JPのサーバー側でエラーが発生している
                                case 'pg_wrong': // 決済代行会社のサーバー側でエラーが発生している
                                case 'not_found': // リクエスト先が存在しないことを示す
                                case 'not_allowed_method': // 許可されていないHTTPメソッド
                                case 'over_capacity': // レートリミットに到達
                                default:
                                    $errorMessage = 'システムのメンテナンス中のため、ご注文を承ることができませんでした。お手数ですが、数時間後に再度お試し下さい。';
                                    break;
                            }
                        }
                    }
                }
                catch (\Exception $e)
                {
                    echo $e->getMessage();
                    // sendErrorMail($e);
                    $errorMessage = 'システムのメンテナンス中のため、ご注文を承ることができませんでした。お手数ですが、数時間後に再度お試し下さい。';
                }
            }
        }

        if (empty($inputErrors) && ($errorMessage === ''))
        {
            /*
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
            */
        }

        // checkinItems($dbc, $userId);
    // }
    /*
    else
    {
        redirectToPage("cart.php?cart_type=$intCartType");
    }
    */
}
/*
else
{
    unset($_SESSION['payment_method']);
}
*/

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
