<?php

function generateCartText(&$totalPayment)
{
    global $dbc;

    $text = '';

    $cCartItem = $_SESSION['cart_item_count'];
    if ($cCartItem > 0)
    {
        $totalWinePrice = 0;
        $i              = 1;
        while ($i <= $cCartItem)
        {
            $itemType    = (isset($_SESSION['cart_item_' . $i . '_type']) ? $_SESSION['cart_item_' . $i . '_type'] : '');
            $itemName    = $_SESSION['cart_item_' . $i . '_name'];
            $itemNumber  = $_SESSION['cart_item_' . $i . '_id'];
            $producer    = $_SESSION['cart_item_' . $i . '_producer'];
            $quantity    = $_SESSION['cart_item_' . $i . '_quantity'];
            $amount      = $_SESSION['cart_item_' . $i . '_price'];
            $totalAmount = $amount * $quantity;

            $strQuantity    = number_format($quantity);
            $strAmount      = number_format($amount);
            $strTotalAmount = number_format($totalAmount);

            $text .= "

-------------------------------------------------------------
 #$itemNumber: $itemType
- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 
 $itemName
 by [ $producer ]
-------------------------------------------------------------
 数量: $strQuantity
 単価: ￥$strAmount
 金額: ￥$strTotalAmount";

            $totalWinePrice = $totalWinePrice + $totalAmount;
            ++$i;
        }

        // TODO: Add discount price for lucky bag.

        $shippingCost = $_SESSION['shipping_fee'];
        $coolCost     = isset($_SESSION['cool_fee']) ? $_SESSION['cool_fee'] : 0;
        $totalPayment = ($totalWinePrice + $shippingCost + $coolCost);
        $totalWineTax = floor($totalPayment * 0.08);
        $totalPayment = ($totalPayment + $totalWineTax);

        $strTotalPrice   = number_format($totalWinePrice);
        $strShippingFee  = number_format($shippingCost);
        $strCoolFee      = number_format($coolCost);
        $strTotalTax     = number_format($totalWineTax);
        $strTotalPayment = number_format($totalPayment);

        $text .= "

╋━━━━━━━━━━━━━━━
┃　合計金額
╋━━━━━━━━━━━━━━━
 小　計: ￥$strTotalPrice
 送　料: ￥$strShippingFee
 クール: ￥$strCoolFee
 消費税: ￥$strTotalTax
_____________________________
 合　計: ￥$strTotalPayment
";
    }

    return $text;
}

function generatePaymentMethodText($intTotalPayment)
{
    $text = '';

    $intMethod = $_SESSION['payment_method'];
    if ($intMethod == 1)
    {
        $text .= 'クレジットカード';
    }
    elseif ($intMethod == 2)
    {
        $text .= '銀行振込み';
    }

    return $text;
}

// --------------------------------
// [Required Variables]
//     $dbc
//     $orderId
//     $deadline
// --------------------------------
$name              = $_SESSION['last_name'] . $_SESSION['first_name'];
$deliverTo         = $_SESSION['delivery_last_name'] . $_SESSION['delivery_first_name'] . ' 様';
$postCode          = $_SESSION['delivery_post_code'];
$prefecture        = $_SESSION['delivery_prefecture'];
$shippingAddress   = $_SESSION['delivery_address'];
$phoneNumber       = $_SESSION['delivery_phone'];
$deliveryCompany   = $_SESSION['delivery_company'];
$deliveryDate      = $_SESSION['delivery_date'];
$deliveryTime      = $_SESSION['delivery_time'];
$cartType          = isset($_SESSION['cart_type']) ? $_SESSION['cart_type'] : 0;
$strToday          = date('Y.m.d');
$totalPayment      = 0;
$cartContentsText  = generateCartText($totalPayment);
$paymentMethodText = generatePaymentMethodText($totalPayment);

//----------------------------------------------
// Define variables for mail_body_base.php
//----------------------------------------------

$title        = 'ご注文の確認';
$customerName = "$name 様";

$additionalNote = '';
$stockNote      = '';
if ($cartType < 2)
{
    $additionalNote =
        '火曜日を除き、通常は24時間以内に確定メールが送信されます。
確定メールが届かない場合は、お手数ですがご連絡くださいますようお願い申し上げます。';

    $stockNote =
        '実店舗と在庫を共有しているため、ご注文手続き中、または本メール送信後から商品の在庫を確保するまでの間に、店頭で商品が売れてしまい在庫が確保できない場合がございます。
万が一在庫を確保できなかった場合には、メールにてご連絡いたします。';
}
else
{
    $stockNote =
        '当店の実在庫ではないため、ご注文頂いた商品の確保が出来ない場合がございます。
在庫の確保が出来なかった場合には、メールにてご連絡いたします。';
}


$body = "お客様のご注文内容にお間違いがないかご確認ください。

在庫の確保ができましたら、注文の確定メールを送信いたします。
$additionalNote

$cartContentsText


[ お支払い方法 ]

$paymentMethodText 


[ 配送業者 ]

$deliveryCompany


[ お届け先 ]

$deliverTo
〒$postCode $prefecture $shippingAddress
$phoneNumber


[ お届け希望日時 ]

$deliveryDate $deliveryTime


[ 在庫確保について ]

$stockNote
";

//----------------------------------------------

include_once(dirname(__FILE__) . '/mail_body_base.php');
