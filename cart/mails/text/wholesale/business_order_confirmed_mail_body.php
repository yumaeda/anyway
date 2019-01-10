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

$title        = '注文確定のお知らせ';
$customerName = "$name 御中";
$body         = "お客様のご注文を下記の内容で承りましたのでご連絡申し上げます。

$strOrderedWinesBody


[運送業者]

$deliveryCompany


[ お届け先 ]

$deliverTo
$address


[ お届け希望日時 ]

$deliveryDate $deliveryTime
";

//----------------------------------------------

include_once(dirname(__FILE__) . '/../mail_body_base.php');
