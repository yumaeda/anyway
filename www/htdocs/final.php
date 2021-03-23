<?php

require_once('./includes/config.inc.php');
require_once(MYSQL);

$userId = startCartSession($dbc);

function subscribeToMailMagazine($mail, $firstName, $lastName)
{
    require_once('../includes/Mailchimp.php');

    $apiKey     = '36d49c517896ac4996e00e46252b85fd-us8';
    $listId     = '136d202046';
    $merge_vars = array(
        'FNAME' => $firstName,
        'LNAME' => $lastName
    );  

    $api    = new Mailchimp($apiKey);
    $result = $api->lists->subscribe($listId,
        array('email' => $mail),
        $merge_vars,
        false,
        true,
        false,
        false);

    $fSuccess = FALSE;
    if ($result['email'] == $mail)
    {   
        $fSuccess = TRUE;
    }   

    return $fSuccess;
}

if (!isset($_SESSION['paid_order']))
{
    redirectToPage('payment.php');
}

// Register the customer to the Mail Magazine if he/she wants.
if (isset($_SESSION['mail_magazine']) &&
    ($_SESSION['mail_magazine'] == 1) &&
    isset($_SESSION['email']) &&
    isset($_SESSION['first_name']) &&
    isset($_SESSION['last_name']))
{
    try
    {
        subscribeToMailMagazine($_SESSION['email'], $_SESSION['first_name'], $_SESSION['last_name']);
    }
    catch (\Exception $e)
    {
        require(UTIL);
        sendErrorMail($e);
    }
}

// Send a confirmation email.
// ---------------------------------
$orderId      = $_SESSION['paid_order'];
$email        = $_SESSION['email'];
$subject      = 'Anyway-Grapes: ご注文の確認';
$orderEmail   = 'order@anyway-grapes.jp';
$archiveEmail = 'archive@anyway-grapes.jp';

$customerName = '';
if (isset($_SESSION['first_name']) && isset($_SESSION['last_name']))
{
    $customerName = ($_SESSION['last_name'] . ' ' . $_SESSION['first_name']);
}

require_once('./mails/text/order_confirmation_mail_body.php');
require(E_MAIL);
sendMailAsPlainText(
    $email,
    $customerName,
    $orderEmail,
    $archiveEmail,
    $subject,
    $textMessage
);
// ---------------------------------

clearCartSession();

$pageTitle = '終了｜anyway-grapes.jp';
include('./includes/header.html');
include('./views/final.html');
include('./includes/footer.html');

?>
