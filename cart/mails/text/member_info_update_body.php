<?php

// --------------------------------
// [Required Variables]
// $email
// $fistName
// $lastName
// $lastNamePhonetic
// $firstNamePhonetic
// $phone
// $fullAddress
// --------------------------------

//----------------------------------------------
// Define variables for mail_body_base.php
//----------------------------------------------

$title        = '会員情報変更のお知らせ';
$customerName = "$lastName$firstName 様";
$body         = "お客様により会員情報が変更されましたのでお知らせします。
ご不明な点などございましたら、お気軽にお問い合わせくださいますようお願い申し上げます。


[お名前]

$lastName$firstName


[お名前（ふりがな）]

$lastNamePhonetic$firstNamePhonetic


[電話番号]

$phone


[住所]

$fullAddress


[メールアドレス]

$email


※本メールは、Conceptual Wine Boutique Anyway-Grapesのホームページより会員情報を変更された方にお送りしています。もしお心当たりが無い場合は、その旨mail@anyway-grapes.jpまでご連絡いただければ幸いです。
";

//----------------------------------------------

include_once(dirname(__FILE__) . '/mail_body_base.php');
