<?php

$curDirPath = dirname(__FILE__);
require_once("$curDirPath/defines.php");
require_once("$curDirPath/../../../includes/config.inc.php");
require_once(MYSQL);

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    if (isset($_POST['orderId']) &&
        isset($_POST['trackingId']) &&
        isset($_POST['name']) &&
        isset($_POST['email']) &&
        isset($_POST['delivery_date']) &&
        isset($_POST['delivery_time']) &&
        isset($_POST['address']) &&
        isset($_POST['contents']))
    {
        $strOrderedWines = $_POST['contents'];
        $intOrderId      = $_POST['orderId'];
        $orderId         = 'ANYWAY_WS_' . str_pad($intOrderId, 8, '0', STR_PAD_LEFT);
        $trackingId      = $_POST['trackingId'];
        $name            = $_POST['name'];
        $deliverTo       = "$name 御中";
        $address         = $_POST['address'];
        $deliveryCompany = '佐川急便';
        $deliveryDate    = $_POST['delivery_date'];
        $deliveryTime    = $_POST['delivery_time'];

        $result = mysqli_query($dbc, "CALL set_business_order_tracking_id($intOrderId, '$trackingId')");
        if ($result !== FALSE)
        {
            $subject      = 'Anyway-Grapes: 商品発送のお知らせ';
            $orderMail    = 'order@anyway-grapes.jp';
            $archiveEmail = 'archive@anyway-grapes.jp';

            require_once("$curDirPath/../../../mails/text/wholesale/business_order_shipped_mail_body.php");
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
}

?>
