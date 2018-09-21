<?php

$curDirPath = dirname(__FILE__);
require_once("$curDirPath/defines.php");
require_once("$curDirPath/../../../includes/config.inc.php");
require_once(MYSQL);

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    if (isset($_POST['orderId']) &&
        isset($_POST['name']) &&
        isset($_POST['email']) &&
        isset($_POST['delivery_date']) &&
        isset($_POST['delivery_time']) &&
        isset($_POST['wine_total']) &&
        isset($_POST['shipping_fee']) &&
        isset($_POST['address']) &&
        isset($_POST['contents']))
    {
        $strOrderedWines = $_POST['contents'];
        $orderId         = 'ANYWAY_WS_' . str_pad($_POST['orderId'], 8, '0', STR_PAD_LEFT);
        $name            = $_POST['name'];
        $deliverTo       = "$name 御中";
        $address         = $_POST['address'];
        $deliveryCompany = '佐川急便';
        $deliveryDate    = $_POST['delivery_date'];
        $deliveryTime    = $_POST['delivery_time'];
        $shippingFee     = $_POST['shipping_fee'];
        $totalWinePrice  = $_POST['wine_total'];

        $subject      = 'Anyway-Grapes: 注文確定のお知らせ';
        $orderMail    = 'order@anyway-grapes.jp';
        $archiveEmail = 'archive@anyway-grapes.jp';

        require_once("$curDirPath/../../../mails/text/wholesale/business_order_confirmed_mail_body.php");
        require(E_MAIL);
        sendMailAsPlainText(
            $_POST['email'],
            $deliverTo,
            $orderMail,
            $archiveEmail,
            $subject,
            $textMessage
        );
    }
}

?>
