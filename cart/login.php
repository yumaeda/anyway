<?php

require_once('./includes/config.inc.php');
require_once(MYSQL);

if (!isset($_COOKIE['SESSION']) || (strlen($_COOKIE['SESSION']) !== 32))
{
    $userId = openssl_random_pseudo_bytes(16);
    $userId = bin2hex($userId);

    // Set cookie, which expires in 24 hours.
    $expireDate = time() + (1 * 24 * 60 * 60);
    setcookie('SESSION', $userId, $expireDate, '/', 'anyway-grapes.jp');
}

$returnUrl = 'http://anyway-grapes.jp/store/index.php';
if (isset($_REQUEST['return_url']) && $_REQUEST['return_url'] != '')
{
    $returnUrl = urldecode($_REQUEST['return_url']);
}

$loginErrors = array();
if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
    {
        $email = escape_data($_POST['email'], $dbc);
    }
    else
    {
        $loginErrors['email'] = '正しい形式のメールアドレスを入力してください。';
    }

    if (!empty($_POST['pwd']))
    {
        $pwd = $_POST['pwd'];
    }
    else
    {
        $loginErrors['pwd'] = 'パスワードを入力してください。';
    }

    if (empty($loginErrors))
    {
        $result = mysqli_query($dbc, "CALL get_customer('$email')");
        if ($result !== FALSE)
        {
            if (mysqli_num_rows($result) === 1)
            {
                $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
                if (password_verify($pwd, $row['hash']))
                {
                    session_id($_COOKIE['SESSION']);
                    startCartSession($dbc);

                    $_SESSION['user_id']   = $email;
                    $_SESSION['user_name'] = $row['last_name'] . ' ' . $row['first_name'];

                    $_SESSION['email']          = $email;
                    $_SESSION['email_confirm']  = $email;
                    $_SESSION['first_name']     = $row['first_name'];
                    $_SESSION['last_name']      = $row['last_name'];
                    $_SESSION['first_phonetic'] = $row['first_name_phonetic'];
                    $_SESSION['last_phonetic']  = $row['last_name_phonetic'];
                    $_SESSION['phone']          = $row['phone'];
                    $_SESSION['post_code']      = $row['post_code'];
                    $_SESSION['prefecture']     = $row['prefecture'];
                    $_SESSION['address']        = $row['address'];

                    if (!empty($row['date_of_birth']))
                    {
                        list($wareki, $warekiYear, $warekiMonth, $warekiDate) = explode('/', $row['date_of_birth']);
                        $_SESSION['wareki']       = $wareki;
                        $_SESSION['wareki_year']  = $warekiYear;
                        $_SESSION['wareki_month'] = $warekiMonth;
                        $_SESSION['wareki_date']  = $warekiDate;
                    }

                    // Explicity redirect to home over HTTP.
                    header("Location: $returnUrl");
                    exit();
                }
            }

            mysqli_free_result($result);
        }

        $loginErrors['login'] = '入力されたEメール、もしくはパスワードが正しくありません。';
    }
}

$pageTitle = 'ログイン';
include('./includes/header.html');
include('./views/login.html');
include('./includes/footer.html');

?>
