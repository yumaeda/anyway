<?php

// Copied from shipping_confirmation_mail.body.php
function generateTrackingPageUrl($trackingId, $isYamato)
{
    $url = '';

    if (!empty($trackingId) &&
        (strlen($trackingId) === 14) &&
        ($trackingId !== '0000-0000-0000'))
    {
        $yamatoTrackingPageUrl = 'http://jizen.kuronekoyamato.co.jp/jizen/servlet/crjz.b.NQ0010';
        $sagawaTrackingPageUrl = 'http://k2k.sagawa-exp.co.jp/p/web/okurijosearch.do?okurijoNo=';

        $url = $isYamato ?
            "$yamatoTrackingPageUrl?id=$trackingId" :
            "$sagawaTrackingPageUrl?okurijoNo=$trackingId";
    }

    return $url;
}

function generateShippedItemsText($dbc, $strShippedItems)
{
    $text = '';
    if (!empty($strShippedItems))
    {
        $rgstrItem  = explode(';', $strShippedItems);

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

                prepareNextQuery($dbc);

                if ($itemNumber > 50000)
                {
                    $setId  = $itemNumber - 50000;
                    $result = mysqli_query($dbc, "CALL get_wine_set('$setId')");
                    if ($result !== FALSE)
                    {
                        $row      = mysqli_fetch_array($result, MYSQLI_ASSOC);
                        $itemName = $row['name'];
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

                        mysqli_free_result($result);
                    }
                }

                $strQuantity = number_format($quantity);

                $text .= "

-------------------------------------------------------------
 #$itemNumber: $itemType
- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 
 $itemName
 by [ $producer ]
-------------------------------------------------------------
 数量: $strQuantity";
            }
        }
    }

    return $text;
}


// --------------------------------
// [Required Variables]
//     $dbc
//     $cartContents
//     $orderId
//     $name
//     $trackingId
//     $trackingId2
//     $deliverTo
//     $postCode
//     $prefecture
//     $shippingAddress
//     $phoneNumber
//     $deliveryCompany
//     $deliveryDate
//     $deliveryTime
// --------------------------------
$cartContentsText = generateShippedItemsText($dbc, $cartContents);

//----------------------------------------------
// Define variables for mail_body_base.php
//----------------------------------------------

$title            = 'ご利用頂きありがとうございました';
$customerName     = "$name 様";
$isYamato         = ($deliveryCompany === 'ヤマト運輸');
$trackingPageUrl  = generateTrackingPageUrl($trackingId, $isYamato); 
$trackingPageUrl2 = generateTrackingPageUrl($trackingId2, $isYamato); 

$body = "お買い上げ頂いた商品は、お手元に届きましたでしょうか?
注文したものとは別の商品が届いたりしてご迷惑をお掛けしておりましたら、誠に恐れ入りますがご連絡頂けますでしょうか？

$cartContentsText

またのご利用を心よりお待ち申し上げます。
その他、ご質問等ございましたらお気軽にお問合せください。 


[運送業者]

$deliveryCompany : お荷物の配送状況は下記のページよりご確認いただけます。
$trackingPageUrl
$trackingPageUrl2


[ お届け先 ]

$deliverTo
〒$postCode $prefecture $shippingAddress
$phoneNumber


[ お届け希望日時 ]

$deliveryDate $deliveryTime
";

//----------------------------------------------

include_once(dirname(__FILE__) . '/mail_body_base.php');
