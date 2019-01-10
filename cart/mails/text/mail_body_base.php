<?php

//--------------------------------------
// Required Variables
//--------------------------------------
// $title
// $orderId
// $customerName
// $body

$strCustomerName = '';
if (isset($customerName))
{
   $strCustomerName = "$customerName
";
}

$strOrderId = '';
if (isset($orderId))
{
    $strOrderId = "受付番号: $orderId
";
}

$textMessage = "
━━…━━…━━…━━

 $title

━━…━━…━━…━━


$strCustomerName$strOrderId" . "この度はAnyway-Grapesをご利用頂きまして、誠にありがとうございます。

$body


■〓■〓■〓■〓■〓■〓■〓■〓■〓■〓■〓■〓

　A｜n｜y｜w｜a｜y｜-｜G｜r｜a｜p｜e｜s
　conceputual wine boutique

　〒156-0052 東京都世田谷区経堂 2-13-1 B1F
　TEL: 03-6413-9737
　FAX: 03-6413-9736
　URL: www.anyway-grapes.jp

■〓■〓■〓■〓■〓■〓■〓■〓■〓■〓■〓■〓
";
