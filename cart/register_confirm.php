<?php

require('./includes/config.inc.php');
require(MYSQL);

session_start();
if (isSessionTimeout())
{
    session_regenerate_id(true);
    redirectToPage('timeout.php');
}

$lastName          = $_SESSION['last_name'];
$firstName         = $_SESSION['first_name'];
$lastNamePhonetic  = $_SESSION['last_name_phonetic'];
$firstNamePhonetic = $_SESSION['first_name_phonetic'];
$dateOfBirth       = $_SESSION['date_of_birth'];
$phone             = $_SESSION['phone'];
$postCode          = $_SESSION['post_code'];
$prefecture        = $_SESSION['prefecture'];
$address           = $_SESSION['address'];
$fullAddress       = $_SESSION['full_address'];
$email             = $_SESSION['email'];

if (empty($lastName) ||
    empty($firstName) ||
    empty($lastNamePhonetic) ||
    empty($firstNamePhonetic) ||
    empty($email) ||
    empty($postCode) ||
    empty($prefecture) ||
    empty($address) ||
    empty($phone) ||
    empty($dateOfBirth))
{
    redirectToPage('./register.php');
}

$pageTitle = '会員情報の確認';
include('./includes/header.html');

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $hashedPwd = $_SESSION['hashed_pwd'];
    if (!empty($hashedPwd))
    {
        $result = mysqli_query($dbc, "CALL add_customer('$email', '$hashedPwd', '$lastName', '$firstName', '$lastNamePhonetic', '$firstNamePhonetic', '$dateOfBirth', '$postCode', '$prefecture', '$address', '$phone')");
        if (mysqli_affected_rows($dbc) == 1)
        {
            $subject    = 'Anyway-Grapes: 会員登録完了のお知らせ';
            $anywayEmail = 'mail@anyway-grapes.jp';

            require_once('./mails/text/membership_confirmation_mail_body.php');
            require(E_MAIL);
            sendMailAsPlainText(
                $email,
                "$lastName $firstName 様",
                $anywayEmail,
                $anywayEmail,
                $subject,
                $textMessage
            );

            clearCartSession();

            echo '
            <span class="engFont" style="font-size:15px;">Registration Completed </span>&nbsp;/&nbsp;<span style="font-size:10px;">会員登録完了</span>
            <hr class="lineThin" />
            <p>' .
                'この度は会員登録依頼をいただきまして、誠にありがとうございました。' .
                '<br /><br />' .
                'お客様宛に、「会員登録の完了のお知らせ」メールが送信されましたのでご確認下さい。「会員登録の完了のお知らせ」メールが届かない場合は、お手数ですがmail@anyway-grapes.jpまでご連絡ください。
                <br /><br />
            </p>
            <br /><br />
            <a href="http://anyway-grapes.jp">トップページに戻る</a>';
        }
        else
        {
            trigger_error('Unexpected error has occurred. We apologize for the inconvenience.');
        }
    }
}
else
{
    include('./views/register_confirm.html');
}

include('./includes/footer.html');

?>
