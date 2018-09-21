<?php

require_once('./includes/config.inc.php');
require_once(MYSQL);

$userId    = startCartSession($dbc); 
$pwdErrors = array();
$errorMsg  = '';

if ($_SERVER['REQUEST_METHOD'] === 'GET')
{
    if (isset($_GET['t']) && (strlen($_GET['t']) === 64))
    {
        $token = $_GET['t'];

        $result = mysqli_query($dbc, "CALL get_user_id_by_token('$token')");
        if ($result !== FALSE)
        {
            if (mysqli_num_rows($result) === 1)
            {
                $row    = mysqli_fetch_array($result, MYSQLI_ASSOC);
                $_SESSION['reset_email'] = $row['email'];

                prepareNextQuery($dbc);
                mysqli_query($dbc, "CALL remove_access_token('$token')");
            }
            else
            {
                $errorMsg = '
                    申し込みをされてから15分以上経過したため、パスワードをリセットする事ができません。
                    <br />
                    お手数ですが、再度申し込みをお願いいたします。
                ';
            }

            mysqli_free_result($result);
        }
        else
        {
            $errorMsg = '予期せぬエラーが発生しため、ページを表示する事が出来ません。.';
        }
    }
    else
    {
        $errorMsg = 'エラーが発生しため、ページを表示する事が出来ません。.';
    }
}
elseif ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    if (isset($_SESSION['reset_email']))
    {
        $errorMsg = '';

        if (preg_match('/^(\w*(?=\w*\d)(?=\w*[a-z])(?=\w*[A-Z])\w*){6,}$/', $_POST['pwd']))
        {
            if ($_POST['pwd'] === $_POST['confirmation_pwd'])
            {
                $hashedPwd = password_hash($_POST['pwd'], PASSWORD_BCRYPT);
            }
            else
            {
                $pwdErrors['confirmation_pwd'] = '確認用のパスワードがパスワードと一致しませんでした。';
            }
        }
        else
        {
            $pwdErrors['pwd'] = '指定された形式のパスワードを入力してください。';
        }

        if (empty($pwdErrors))
        {
            $userId = $_SESSION['reset_email'];

            $result = mysqli_query($dbc, "CALL update_customer_password('$userId', '$hashedPwd')");
            if (($result !== FALSE) && (mysqli_affected_rows($dbc) == 1))
            {
                $pageTitle = 'パスワードのリセット';
                include('./includes/header.html');

                echo '
                    <p>お客様のパスワードがリセットされました。</p>
                    <br /><br />
                    <a href="http://anyway-grapes.jp/store/" class="jpnFont">オンラインストアに戻る</a>
                ';

                include('./includes/footer.html');

                exit();
            }
            else
            {
                trigger_error('パスワードのリセットに失敗しました。');;
            }
        }
    }
    else
    {
        $errorMsg = 'エラーが発生しため、ページを表示する事が出来ません。.';
    }
}

$pageTitle = 'パスワードのリセット';
include('./includes/header.html');
include('./views/reset_password.html');
include('./includes/footer.html');

?>
