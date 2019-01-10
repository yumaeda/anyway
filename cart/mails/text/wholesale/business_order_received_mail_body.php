<?php

// --------------------------------
// [Required Variables]
//     $name
//     $deliveryCompany
//     $deliverTo
//     $address
//     $deliveryDate
//     $deliveryTime
// --------------------------------

$fIncludePrice = TRUE;
$curDirPath    = dirname(__FILE__);
include_once("$curDirPath/ordered_wine_list_text.php");

//----------------------------------------------
// Define variables for mail_body_base.php
//----------------------------------------------

$title        = 'ご注文の確認';
$customerName = "$name 御中";
$body         = "お客様のご注文内容にお間違いがないかご確認ください。

在庫の確保ができましたら、注文の確定メールを送信いたします。
火曜日を除き、通常は24時間以内に確定メールが送信されます。
確定メールが届かない場合は、お手数ですがご連絡くださいますようお願い申し上げます。

$strOrderedWinesBody


[ 配送業者 ]

$deliveryCompany


[ お届け先 ]

$deliverTo
$address


[ お届け希望日時 ]

$deliveryDate $deliveryTime


[ 在庫確保について ]

実店舗と在庫を共有しているため、ご注文手続き中、または本メール送信後から商品の在庫を確保するまでの間に、店頭で商品が売れてしまい在庫が確保できない場合がございます。
万が一在庫を確保できなかった場合には、メールにてご連絡いたします。
";

//----------------------------------------------

include_once(dirname(__FILE__) . '/../mail_body_base.php');
