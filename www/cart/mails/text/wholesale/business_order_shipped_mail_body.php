<?php

// --------------------------------
// [Required Variables]
//     $trackingId
//     $name
//     $deliverTo
//     $address
//     $deliveryCompany
//     $deliveryDate
//     $deliveryTime
// --------------------------------

$fIncludePrice = FALSE;
$curDirPath    = dirname(__FILE__);
include_once("$curDirPath/ordered_wine_list_text.php");

//----------------------------------------------
// Define variables for mail_body_base.php
//----------------------------------------------

$title        = '商品発送のお知らせ';
$customerName = "$name 御中";
$body         = "お客様のご注文のワインを発送いたしました。

$strOrderedWinesBody


[運送業者]

$deliveryCompany #$trackingId


[ お届け先 ]

$deliverTo
$address


[ お届け希望日時 ]

$deliveryDate $deliveryTime
";

//----------------------------------------------

include_once(dirname(__FILE__) . '/../mail_body_base.php');
