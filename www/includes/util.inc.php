<?php
namespace Yumaeda\Shipping\YamatoShippingFee;

$curDirPath = dirname(__FILE__);
require_once("$curDirPath/shipping.inc.php");

$maintenanceError = '大変申し訳ございません。只今、決済システムのメンテナンスのため、クレジットカードによる決済がご利用頂けません。';

$jpnRegions = array(
    '北海道' => HOKKAIDO_STR,

    '青森県'   => TOHOKU_STR,
    '岩手県'   => TOHOKU_STR,
    '秋田県'   => TOHOKU_STR,
    '宮城県'   => TOHOKU_STR,
    '山形県'   => TOHOKU_STR,
    '福島県'   => TOHOKU_STR,

    '茨城県'   => KANTO_STR,
    '栃木県'   => KANTO_STR,
    '群馬県'   => KANTO_STR,
    '埼玉県'   => KANTO_STR,
    '千葉県'   => KANTO_STR,
    '東京都'   => KANTO_STR,
    '神奈川県' => KANTO_STR,
    '山梨県'   => KANTO_STR,

    '新潟県'   => SHINETSU_STR,
    '長野県'   => SHINETSU_STR,

    '岐阜県'   => TOKAI_STR,
    '静岡県'   => TOKAI_STR,
    '愛知県'   => TOKAI_STR,
    '三重県'   => TOKAI_STR,

    '富山県'   => HOKURIKU_STR,
    '石川県'   => HOKURIKU_STR,
    '福井県'   => HOKURIKU_STR,

    '滋賀県'   => KANSAI_STR,
    '京都府'   => KANSAI_STR,
    '大阪府'   => KANSAI_STR,
    '兵庫県'   => KANSAI_STR,
    '奈良県'   => KANSAI_STR,
    '和歌山県' => KANSAI_STR,

    '鳥取県'   => CYUGOKU_STR,
    '島根県'   => CYUGOKU_STR,
    '岡山県'   => CYUGOKU_STR,
    '広島県'   => CYUGOKU_STR,
    '山口県'   => CYUGOKU_STR,

    '徳島県'   => SHIKOKU_STR,
    '香川県'   => SHIKOKU_STR,
    '愛媛県'   => SHIKOKU_STR,
    '高知県'   => SHIKOKU_STR,

    '福岡県'   => KYUSYU_STR,
    '佐賀県'   => KYUSYU_STR,
    '長崎県'   => KYUSYU_STR,
    '大分県'   => KYUSYU_STR,
    '熊本県'   => KYUSYU_STR,
    '宮崎県'   => KYUSYU_STR,
    '鹿児島県' => KYUSYU_STR,

    '沖縄県'   => OKINAWA_STR
);

function getBoxCountTable($qty)
{
    $maxQty      = 12;
    $numOf60Box  = 0;
    $numOf80Box  = 0;
    $numOf140Box = ($qty > $maxQty) ? (int)($qty / $maxQty) : 0;

    $qtyRemain = $qty % $maxQty;
    if (($qtyRemain === 1) || ($qtyRemain === 2))
    {
        ++$numOf60Box;
    }
    elseif (($qtyRemain >= 3) && ($qtyRemain <= 6))
    {
        ++$numOf80Box;
    }
    else
    {
        ++$numOf140Box;
    }

    return array
    (
        '60'  => $numOf60Box,
        '80'  => $numOf80Box,
        '140' => $numOf140Box
    );
}

function getShippingFee($price, $qty, $prefecture, $setQty, $freeShippingPrice)
{
    global $shipping_fees, $jpnRegions;

    if ($qty <= 0)
    {
        return 0;
    }

    $region = $jpnRegions[$prefecture];
    $intFee = $shipping_fees[$region];

    if ($qty <= 12)
    {
        if (($price < $freeShippingPrice) && ($setQty == 0))
        {
            return $intFee;
        }
        else
        {
            if (($region == HOKKAIDO_STR) ||
                ($region == KYUSYU_STR) ||
                ($region == OKINAWA_STR))
            {
                return $intFee - $shipping_fees[KANTO_STR];
            }
            else
            {
                return 0;
            }
        }
    }

    if ($price < $freeShippingPrice)
    {
        $extra = $qty % 12;
        $cBox  = intval($qty / 12, 10);
        if ($extra > 0)
        {
            ++$cBox;
        }

        return $intFee * $cBox;
    }

    return getShippingFee($freeShippingPrice, 12, $prefecture, 0, $freeShippingPrice) +
           getShippingFee($price - $freeShippingPrice, $qty - 12, $prefecture, 0, $freeShippingPrice);
}

function getShippingFeeByBoxCount($cBox, $cFreeBox, $prefecture)
{
    global $shipping_fees, $jpnRegions;

    $region          = $jpnRegions[$prefecture];
    $intFee          = $shipping_fees[$region];
    $freeShippingFee = 0;

    if (($region == HOKKAIDO_STR) ||
        ($region == KYUSYU_STR) ||
        ($region == OKINAWA_STR))
    {
        $freeShippingFee = ($intFee - $shipping_fees[KANTO_STR]);
    }

    $cBoxToCharge = ($cBox - $cFreeBox);
    if ($cBoxToCharge < 0)
    {
        $cBoxToCharge = 0;
    }

    return (($cBoxToCharge * $intFee) + ($cFreeBox * $freeShippingFee));
}

function getHappyBoxShippingFee($price, $qty, $prefecture)
{
    if ($qty <= 0)
    {
        return 0;
    }

    global $shipping_fees, $jpnRegions;

    $region = $jpnRegions[$prefecture];
    $intFee = $shipping_fees[$region];

    if (($qty == 6) || ($qty == 12))
    {
        if (($region == HOKKAIDO_STR) ||
            ($region == KYUSYU_STR) ||
            ($region == OKINAWA_STR))
        {
            return $intFee - $shipping_fees[KANTO_STR];
        }
        else
        {
            return 0;
        }
    }

    return getShippingFee($price, $qty, $prefecture, 0, FALSE);
}

function getCooledPackageFee($qty)
{
    global $cool_fee;

    $fee = 0;
    
    $boxCountTable = getBoxCountTable($qty);
    $fee += ($cool_fee * $boxCountTable['60']);
    $fee += ($cool_fee * $boxCountTable['80']);
    $fee += ($cool_fee * $boxCountTable['140']);

    return $fee;
}

function getBoxCountForYamato($qty, $fCool)
{
    $cBox = 0;

    if ($qty > 0)
    {
        $cMaxBottles = $fCool ? 9 : 12;
        $cBox        = floor($qty / $cMaxBottles);
        $cExtra      = $qty % $cMaxBottles;
        if ($cExtra > 0)
        {
            ++$cBox;
        }
    }

    return $cBox;
}

function getCoolFeeForYamato($qty)
{
    $yamato_fee = new YamatoShippingFee('TOKYO', $qty, true, 5000); 

    return $yamato_fee->getRefrigeratedDeliveryFee($qty);
}

function getShippingFeeForYamato($qty, $price, $prefecture, $fCool, $freeShippingPrice, $strDeliveryDate)
{
    global $jpnRegions, $shipping_fees;

    $rgintYamatoShipping = $shipping_fees;
    $cBox                = getBoxCountForYamato($qty, $fCool);
    $cFreeBox            = ($price > 0) ? floor($price / $freeShippingPrice) : 0;

    if ($cFreeBox > $cBox)
    {
        $cFreeBox = $cBox;
    }
    $cBox -= $cFreeBox;

    $region   = $jpnRegions[$prefecture];
    $intFee   = $rgintYamatoShipping[$region];
    $extraFee = 0;
    if (($region == HOKKAIDO_STR) ||
        ($region == KYUSYU_STR) ||
        ($region == OKINAWA_STR))
    {
        $extraFee = $cFreeBox * ($intFee - $rgintYamatoShipping[KANTO_STR]);
    }

    $intTotal = ($intFee * $cBox) + $extraFee;

    return $intTotal;
}

function getShippingFeeForWineSet($setQty, $totalQty, $prefecture, $fCool, $strDeliveryDate)
{
    global $jpnRegions, $shipping_fees;

    $rgintYamatoShipping = $shipping_fees;
    $cBox                = getBoxCountForYamato($totalQty, $fCool);
    $cFreeBox            = $setQty;

    if ($cFreeBox > $cBox)
    {
        $cFreeBox = $cBox;
    }
    $cBox -= $cFreeBox;

    $region   = $jpnRegions[$prefecture];
    $intFee   = $rgintYamatoShipping[$region];
    $extraFee = 0;
    if (($region == HOKKAIDO_STR) ||
        ($region == KYUSYU_STR) ||
        ($region == OKINAWA_STR))
    {
        $extraFee = $cFreeBox * ($intFee - $rgintYamatoShipping[KANTO_STR]);
    }

    $intTotal = ($intFee * $cBox) + $extraFee;

    return $intTotal;
}

//------------------------------------------------


function getOrderId()
{
    global $curDirPath;

    $idFilePath = "$curDirPath/orderId.txt";
    $idSuffix   = file_get_contents($idFilePath) + 1;

    return date('Ymd') . "-" . sprintf('%010d', $idSuffix);
}

function incrementOrderId()
{
    global $curDirPath;

    $idFilePath = "$curDirPath/orderId.txt";
    $idSuffix   = file_get_contents($idFilePath) + 1;
    file_put_contents($idFilePath, $idSuffix);
}

function generateReservationId()
{
    global $curDirPath;

    $idFilePath = "$curDirPath/reservationId.txt";
    $idSuffix   = file_get_contents($idFilePath) + 1;
    file_put_contents($idFilePath, $idSuffix);

    return date('00000000') . "-" . sprintf('%010d', $idSuffix);
}

function convertWarekiToYear($nengou, $year)
{
    $fullYear = 0;

    if ($nengou === '平成')
    {
        $fullYear = $year + 1988;
    }
    elseif ($nengou === '昭和')
    {
        $fullYear = $year + 1925;
    }
    elseif ($nengou === '大正')
    {
        $fullYear = $year + 1911;
    }

    return $fullYear;
}

function getAge($nengou, $year, $month, $date)
{
    $birthYear = convertWarekiToYear($nengou, $year);
    $strMonth  = intval($month);
    $strDate   = intval($date);

    if ($strMonth < 10)
    {
        $strMonth = '0' . $strMonth;
    }
    if ($strDate < 10)
    {
        $strDate = '0' . $strDate;
    }

    $birthDay = new DateTime("$birthYear-$strMonth-$strDate 00:00:00");
    $today    = new DateTime('00:00:00');
    $diff     = $today->diff($birthDay);

    return ($diff->y);
} 

function sendErrorMail($e)
{
    ob_start();
    var_dump($e);
    $result = ob_get_clean();

    error_log($result, 1, 'sysadm@anyway-grapes.jp');
}

function handleErrorResponseException($e, &$inputErrors)
{
    global $maintenanceError;

    $error = $e->data->error;
    switch ($error->causedBy)
    {
    case 'buyer':
        if ($error->type == 'card_error')
        {
            switch ($error->code)
            {
            case 'invalid_number':
            case 'incorrect_number':
                $inputErrors['card_number'] = 'カード番号が正しくありません。';
                break;
            case 'invalid_name':
                $inputErrors['holder_name'] = 'カードの名義が正しくありません。';
                break;
            case 'invalid_expiry_month':
            case 'invalid_expiry_year':
            case 'invalid_expiry':
            case 'incorrect_expiry':
                $inputErrors['expiration'] = 'カードの有効期限が正しくありません。';
                break;
            case 'invalid_cvc':
            case 'incorrect_cvc':
                $inputErrors['cvc'] = 'CVCセキュリティコードが正しくありません。';
                break;
            case 'card_declined':
            case 'missing':
                $inputErrors['card_number'] = '決済ができませんでした。お手数ですが、お客様のクレジットカード会社にお問い合わせ下さい。';
                break;
            case 'processing_error':
                $inputErrors['card_number'] = '決済処理の途中でエラーが発生しました。インターネットに接続されている事を確認の上、もう決済処理をやり直して下さい。';
                break;
            default:
                sendErrorMail($e);
                break;
            }
        }
        break;
    case 'insufficient': // Improper usage of WebPay API
    case 'missing':      // Requested WebPay object not found
    case 'service':      // WebPay service is down
    default:             // Unexpected error
        $inputErrors['card_number'] = $maintenanceError;
        sendErrorMail($e);
        break;
    }
}

function handleErrorResponseException_Eng($e, &$inputErrors)
{
    global $maintenanceError;

    $error = $e->data->error;
    switch ($error->causedBy)
    {
    case 'buyer':
        if ($error->type == 'card_error')
        {
            switch ($error->code)
            {
            case 'invalid_number':
            case 'incorrect_number':
                $inputErrors['card_number'] = 'Credit card number is not correct.';
                break;
            case 'invalid_name':
                $inputErrors['holder_name'] = 'Name of the card holder is not correct.';
                break;
            case 'invalid_expiry_month':
            case 'invalid_expiry_year':
            case 'invalid_expiry':
            case 'incorrect_expiry':
                $inputErrors['expiration'] = 'Expiration month / year is not correct.';
                break;
            case 'invalid_cvc':
            case 'incorrect_cvc':
                $inputErrors['cvc'] = 'Security code is not correct.';
                break;
            case 'card_declined':
            case 'missing':
                $inputErrors['card_number'] = 'Fail to complete transaction.  Please contact your credit card company.';
                break;
            case 'processing_error':
                $inputErrors['card_number'] = 'Error occurred during the transaction.  Please make sure you are connected to the Internet and try it again.';
                break;
            default:
                sendErrorMail($e);
                break;
            }
        }
        break;
    case 'insufficient': // Improper usage of WebPay API
    case 'missing':      // Requested WebPay object not found
    case 'service':      // WebPay service is down
    default:             // Unexpected error
        $inputErrors['card_number'] = $maintenanceError;
        sendErrorMail($e);
        break;
    }
}
