<?php

// Get tax rate from config file.
$curDir = dirname(__FILE__);
$config = include("$curDir/../../config.php");
$taxRate = $config['tax']['rate']();

function generateConfirmedItemsText($dbc, $strConfirmedItems)
{
    global $memberDiscount, $shippingFee, $totalWinePrice, $paymentMethod, $taxRate;

    $text = '';
    if (!empty($strConfirmedItems))
    {
        $rgstrItem = explode(';', $strConfirmedItems);

        $cItem = count($rgstrItem);
        for ($i = 0; $i < $cItem; ++$i)
        {
            $rgstrToken  = explode('#', $rgstrItem[$i]);
            if (count($rgstrToken) === 2)
            {
                $itemName   = '';
                $itemType   = '';
                $producer   = '';
                $itemNumber = $rgstrToken[0];
                $quantity   = $rgstrToken[1];
                $amount     = 0;

                prepareNextQuery($dbc);

                if ($itemNumber > 50000)
                {
                    $setId  = $itemNumber - 50000;
                    $result = mysqli_query($dbc, "CALL get_wine_set('$setId')");
                    if ($result !== FALSE)
                    {
                        $row      = mysqli_fetch_array($result, MYSQLI_ASSOC);
                        $itemName = $row['name'];
                        $amount   = $row['set_price'];
                        mysqli_free_result($result);
                    }
                }
                else
                {
                    $result = mysqli_query($dbc, "CALL get_wine('$itemNumber')");
                    if ($result !== FALSE)
                    {
                        $row      = mysqli_fetch_array($result, MYSQLI_ASSOC);
                        $itemName = $row['vintage'] . ' ' . $row['combined_name_jpn'];
                        $itemType = $row['type'];
                        $producer = $row['producer_jpn'];

                        $amount = $row['price'];
                        if (($memberDiscount == 1) || ($memberDiscount == 4))
                        {
                            $amount = $row['member_price'];
                        }
                        if (($memberDiscount == 3) || ($memberDiscount == 4))
                        {
                            if ($row['etc'] == 'Special Offer')
                            {
                                $amount = floor($row['price'] * 0.7);
                            }
                        }

                        mysqli_free_result($result);
                    }
                }

                $strQuantity    = number_format($quantity);
                $strAmount      = number_format($amount);
                $strTotalAmount = number_format($quantity * $amount);

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
            }
        }

        $unTaxedTotal = ($totalWinePrice + $shippingFee);
        $grandTotal   = floor((1 + $taxRate) * $unTaxedTotal);
        $tax          = $grandTotal - $unTaxedTotal;

        // TODO: Add discount price for lucky bag.

        $strTotalPrice   = number_format($totalWinePrice);
        $strShippingFee  = number_format($shippingFee);
        $strTotalTax     = number_format($tax);
        $strTotalPayment = number_format($grandTotal);

        $text .= "

╋━━━━━━━━━━━━━━━
┃　合計金額
╋━━━━━━━━━━━━━━━
 小計:　　　　　￥$strTotalPrice
 送料＆クール:　￥$strShippingFee
 消費税:　　　　￥$strTotalTax
_____________________________
 合計:　　　　　￥$strTotalPayment
";
    }

    return $text;
}

function generatePaymentMethodText($intTotalPayment)
{
    global $paymentMethod;

    $strDeadline = getStrDeadline();
    $text        = '';
 
    if ($paymentMethod == 1)
    {
        $text = 'クレジットカードでのお支払いを受け付けました。
発送終了後に、再度メールでお知らせいたします。';
    }
    elseif ($paymentMethod == 2)
    {
        $strPayment = number_format($intTotalPayment);

        $text = "以下の銀行口座に商品の代金 $strPayment 円 をお振込み下さい（振込み手数料はお客様持ちでお願いいたします）。
入金の確認が取れ次第、商品を発送致します（発送終了後に、再度メールでお知らせいたします）。

昭和信用金庫 下高井戸支店(013)
普）0348121
ユ）セイヤ

お支払い期日：$strDeadline

※支払い期日までに入金がない場合は注文をキャンセルさせて頂きますのでご了承ください（支払期日が祝日の場合、期日直後の平日を支払い期限とさせて頂きます）。";
    }

    return $text;
}


// --------------------------------
// [Required Variables]
//     $dbc
//     $cartContents
//     $orderId
//     $name
//     $deliverTo
//     $postCode
//     $prefecture
//     $phoneNumber
//     $shippingAddress
//     $deliveryCompany
//     $deliveryDate
//     $deliveryTime
// --------------------------------
$strToday          = date('Y.m.d');
$totalPayment      = floor((1 + $taxRate) * ($totalWinePrice + $shippingFee));
$cartContentsText  = generateConfirmedItemsText($dbc, $cartContents);
$paymentMethodText = generatePaymentMethodText($totalPayment);

//----------------------------------------------
// Define variables for mail_body_base.php
//----------------------------------------------

$title        = '注文確定のお知らせ';
$customerName = "$name 様";
$body         = "お客様のご注文を下記の内容で承りましたのでご連絡申し上げます。

$cartContentsText


[ 支払い方法 ]

$paymentMethodText


[運送業者]

$deliveryCompany


[ お届け先 ]

$deliverTo
〒$postCode $prefecture $shippingAddress
$phoneNumber


[ お届け希望日時 ]

$deliveryDate $deliveryTime
";

//----------------------------------------------

include_once(dirname(__FILE__) . '/mail_body_base.php');
