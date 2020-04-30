<?php

// --------------------------------
// [Required Variables]
//     $restockedWineHash
// --------------------------------
$strToday            = date('Y.m.d');
$restockWineListText = generateRestockedItemsText($restockedWineHash);
$textMessage         = "

入荷商品のご案内
$strToday

いつもAnyway Grapesをご利用いただきまして、誠にありがとうございます。お客様がお気に入りに登録されている生産者のワインが入荷しました。

$restockWineListText


[ 連絡先 ]

Conceputual Wine Boutique Anyway-Grapes
〒156-0052 東京都世田谷区経堂 2-13-1 B1F

Url:   http://anyway-grapes.jp
Email: mail@anyway-grapes.jp
Tel:   03-6413-9737
Fax:   03-6413-9736

";
