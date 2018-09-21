<?php

require_once('./includes/config.inc.php');
require_once(MYSQL);

$fRequestSent = FALSE;
$emailErrors  = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
    {
        $email = escape_data($_POST['email'], $dbc);
    }
    else
    {
        $emailErrors['email'] = '正しい形式のメールアドレスを入力してください。';
    }

    if (empty($emailErrors))
    {
        $result = mysqli_query($dbc, "CALL get_customer('$email')");
        if ($result !== FALSE)
        {
            $fEmailFound = (mysqli_num_rows($result) === 1);
            if ($fEmailFound)
            {
                $token = openssl_random_pseudo_bytes(32);
                $token = bin2hex($token);

                prepareNextQuery($dbc);

                mysqli_query($dbc, "CALL refresh_access_token('$email', '$token')");

                $cAffectedRow = mysqli_affected_rows($dbc);
                if (($cAffectedRow === 1) || ($cAffectedRow === 2))
                {
                    $resetPageUrl = 'https://' . BASE_URL . 'reset_password.php?t=' . $token;

                    $subject     = 'Anyway-Grapes: パスワードのリセット';
                    $sysadmEmail = 'sysadm@anyway-grapes.jp';

                    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
                    $name = ($row['last_name'] . ' ' .  $row['first_name']);

                    require_once('./mails/text/reset_pwd_mail_body.php');
                    require(E_MAIL);

                    sendMailAsPlainText(
                        $email,
                        "$name 様",
                        $sysadmEmail,
                        $sysadmEmail,
                        $subject,
                        $textMessage
                    );

                    $fRequestSent = TRUE;
                }
            }
            else
            {
                $emailErrors['email'] = '指定されたメールアドレスが見つかりません。';
            }

            mysqli_free_result($result);
        }
        else
        {
            $emailErrors['email'] = '指定されたメールアドレスが見つかりません。';
        }
    }
}

$pageTitle = 'パスワードのリセット';
include('./includes/header.html');

if ($fRequestSent)
{
    echo '
        <p class="jpnFont">
            パスワードのリセットページへのリンクをお客様のメールアドレスに送信しました。 メール本文のリンクをクリックしてパスワードのリセットを行ってください。
        </p><br />
        <a href="http://anyway-grapes.jp/store/" class="jpnFont">オンラインストアに戻る</a>
';
}
else
{
    include('./views/forgot_password.html');
}

include('./includes/footer.html');

?>
