<?php

require('./includes/config.inc.php');
require(MYSQL);

$inputErrors = array();
$inputSrc    = 'SESSION';
$userId      = startCartSession($dbc); 
if (isset($_SESSION['user_id']))
{
    $userEmail = $_SESSION['user_id'];

    if ($_SERVER['REQUEST_METHOD'] === 'GET')
    {
        $result = mysqli_query($dbc, "CALL get_customer('$userEmail')");
        if (($result !== FALSE) && (mysqli_num_rows($result) === 1))
        {
            $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

            $_SESSION['last_name']           = $row['last_name'];
            $_SESSION['first_name']          = $row['first_name'];
            $_SESSION['last_name_phonetic']  = $row['last_name_phonetic'];
            $_SESSION['first_name_phonetic'] = $row['first_name_phonetic'];
            $_SESSION['phone']               = $row['phone'];
            $_SESSION['post_code']           = $row['post_code'];
            $_SESSION['prefecture']          = $row['prefecture'];
            $_SESSION['address']             = $row['address'];
        }
    }
    else
    {
        $email             = $_SESSION['user_id'];
        $lastName          = $_SESSION['last_name'];
        $firstName         = $_SESSION['first_name'];
        $lastNamePhonetic  = $_SESSION['last_name_phonetic'];
        $firstNamePhonetic = $_SESSION['first_name_phonetic'];

        $postCode    = $_POST['post_code'];
        $prefecture  = $_POST['prefecture'];
        $address     = $_POST['address'];
        $phone       = $_POST['phone'];
        $fullAddress = "〒$postCode $prefecture$address";

        $result = mysqli_query($dbc, "CALL update_member_info('$userEmail', '$postCode', '$prefecture', '$address', '$phone')");
        if ($result !== FALSE)
        {
            $subject    = 'Anyway-Grapes: 会員情報の変更';
            $anywayEmail = 'mail@anyway-grapes.jp';

            require_once('./mails/text/member_info_update_body.php');
            require(E_MAIL);
            sendMailAsPlainText(
                $email,
                "$lastName $firstName 様",
                $anywayEmail,
                $anywayEmail,
                $subject,
                $textMessage
            );

            $pageTitle = '会員情報の変更';
            include('./includes/header.html');

            echo '
                <p>お客様の情報が変更されました。</p>
                <br /><br />
                <a href="http://anyway-grapes.jp/store/" class="jpnFont">オンラインストアに戻る</a>
            ';

            include('./includes/footer.html');
            mysqli_close($dbc);
            exit();
        }
    }

    $pageTitle = '会員情報';
    include('./includes/header.html');
    include('./views/customer_info.html');
    include('./includes/footer.html');
}

mysqli_close($dbc);

?>
