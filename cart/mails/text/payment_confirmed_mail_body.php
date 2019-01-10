<?php

function generatePurchasedItemsText($dbc, $strConfirmedItems)
{
    global $memberDiscount, $shippingFee, $totalWinePrice;

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
        $grandTotal   = floor(1.08 * $unTaxedTotal);
        $tax          = $grandTotal - $unTaxedTotal;

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


// --------------------------------
// [Required Variables]
//     $dbc
//     $cartContents
//     $orderId
//     $name
//     $deliverTo
//     $postCode
//     $prefecture
//     $shippingAddress
//     $phoneNumber
//     $deliveryCompany
//     $deliveryDate
//     $deliveryTime
// --------------------------------
$totalPayment     = floor(1.08 * ($totalWinePrice + $shippingFee));
$strTotalPayment  = number_format($totalPayment);
$cartContentsText = generatePurchasedItemsText($dbc, $cartContents);

//----------------------------------------------
// Define variables for mail_body_base.php
//----------------------------------------------

$title        = 'ご入金確認のお知らせ';
$customerName = "$name 様";
$body         = "ご注文頂いた商品の代金 $strTotalPayment 円のご入金を確認いたしました。
商品の発送が終わりましたら、再度メールでお知らせいたします。

$cartContentsText


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
