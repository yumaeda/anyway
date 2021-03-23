<?php

$paidOn = date('Y年m月d日 H時i分');

//----------------------------------------------
// Define variables for mail_body_base.php
//----------------------------------------------

$title        = '決済金額のお知らせ';
$customerName = "$holderName 様";
$body         = "下記の金額を決済頂きましたのでご連絡申し上げます。

[ 支払い方法 ]

クレジットカード


[ お支払い日時 ]

$paidOn


[ お支払金額 ]

$amount 円
";

//----------------------------------------------

include_once(dirname(__FILE__) . '/mail_body_base.php');
