<?php

require_once('./defines.php');
require('../../../includes/config.inc.php');
require(MYSQL);

$inputErrors = array();
$inputSrc    = 'SESSION';
$fRegistered = FALSE;

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $inputSrc = 'POST';

    $name          = getPostValue('name',          true, $inputErrors);
    $namePhonetic  = getPostValue('name_phonetic', true, $inputErrors);
    $phone         = getPostValue('phone',         true, $inputErrors); // Need customized validation.
    $postCode      = getPostValue('post_code',     true, $inputErrors); // Need customized validation.
    $prefecture    = getPostValue('prefecture',    true, $inputErrors);
    $address       = getPostValue('address',       true, $inputErrors);
    $comment       = mysqli_real_escape_string($dbc, $_POST['comment']);
    $hashedPwd     = password_hash($_POST['pwd'], PASSWORD_BCRYPT);

    if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
    {
        $email = escape_data($_POST['email'], $dbc);
    }
    else
    {
        $inputErrors['email'] = '正しいメールアドレスを入力してください。';
    }

    if (empty($inputErrors))
    {
        $result = mysqli_query($dbc, "CALL get_business_customer_count('$email')");
        if ($result !== FALSE)
        {
            list($cCustomer) = mysqli_fetch_array($result);
            mysqli_free_result($result);

            if ($cCustomer > 0)
            {
                $customerExistError   = '指定されたメールアドレスは既に登録されています。';
                $inputErrors['email'] = $customerExistError;
            }
            else
            {
                if (!empty($hashedPwd))
                {
		    prepareNextQuery($dbc);
                    mysqli_query($dbc, "CALL add_business_customer('$email', '$hashedPwd', '$name', '$namePhonetic', '$postCode', '$prefecture', '$address', '$phone', '$comment')");
                    if (mysqli_affected_rows($dbc) == 1)
                    {
                        $fRegistered = TRUE;
                    }
                }
            }
        }
        else
        {
            trigger_error('Unexpected error has occurred. We apologize for the inconvenience.');
        }
    }
}

$pageTitle = '卸先の登録';
include('../../../includes/header.html');

if ($fRegistered)
{
    echo '
    <span class="engFont" style="font-size:15px;">Registration Completed </span>&nbsp;/&nbsp;<span style="font-size:10px;">卸先の登録完了</span>
    <hr class="lineThin" />
    <p>
        卸先の登録が終了しました。
    </p>
    <br /><br />
    <a href="http://sei-ya.jp/admin_home.html">管理者トップに戻る</a>';
}
else
{
    include('./register.html');
}

include('../../../includes/footer.html');

?>
