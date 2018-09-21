<?php

require('./includes/config.inc.php');
require(MYSQL);
require(UTIL);

session_start();

$inputErrors = array();
$inputSrc    = 'SESSION';
if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $inputSrc = 'POST';

    $lastName          = getPostValue('last_name',           true, $inputErrors);
    $firstName         = getPostValue('first_name',          true, $inputErrors);
    $lastNamePhonetic  = getPostValue('last_name_phonetic',  true, $inputErrors);
    $firstNamePhonetic = getPostValue('first_name_phonetic', true, $inputErrors);
    $phone             = getPostValue('phone',               true, $inputErrors); // Need customized validation.
    $postCode          = getPostValue('post_code',           true, $inputErrors); // Need customized validation.
    $prefecture        = getPostValue('prefecture',          true, $inputErrors);
    $address           = getPostValue('address',             true, $inputErrors);

    $wareki      = getPostValue('wareki',       true, $inputErrors);
    $warekiYear  = getPostValue('wareki_year',  true, $inputErrors);
    $warekiMonth = getPostValue('wareki_month', true, $inputErrors);
    $warekiDate  = getPostValue('wareki_date',  true, $inputErrors);

    if (!preg_match("/^[0-9]{1,2}$/", $warekiYear) ||
        (($warekiMonth < 1) || ($warekiMonth > 12)) ||
        (($warekiDate < 1) || ($warekiDate > 31)))
    {
        $inputErrors['wareki'] = '日付を入力してください。';
    }
    else if (getAge($wareki, $warekiYear, $warekiMonth, $warekiDate) < 20)
    {
        $inputErrors['wareki'] = '未成年のお客様は会員登録する事ができません。';
    }

    if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
    {
        $email = escape_data($_POST['email'], $dbc);
    }
    else
    {
        $inputErrors['email'] = '正しいメールアドレスを入力してください。';
    }

    if (!preg_match("/^\d{3}-?\d{4}$/", $postCode))
    {
	$inputErrors['post_code'] = '正しい郵便番号を入力してください。';
    }

    if (preg_match('/^(\w*(?=\w*\d)(?=\w*[a-z])(?=\w*[A-Z])\w*){6,}$/', $_POST['pwd']))
    {
        if ($_POST['pwd'] === $_POST['confirmation_pwd'])
        {
            $hashedPwd = password_hash($_POST['pwd'], PASSWORD_BCRYPT);
        }
        else
        {
            $inputErrors['confirmation_pwd'] = 'パスワードと確認用のパスワードが一致しませんでした。';
        }
    }
    else
    {
        $inputErrors['pwd'] = '指定された形式のパスワードを入力してください。';
    }

    if (empty($inputErrors))
    {
        $result = mysqli_query($dbc, "CALL get_customer_count('$email')");
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
                $_SESSION['last_name']           = $lastName;
                $_SESSION['first_name']          = $firstName;
                $_SESSION['last_name_phonetic']  = $lastNamePhonetic;
                $_SESSION['first_name_phonetic'] = $firstNamePhonetic;
                $_SESSION['phone']               = $phone;
                $_SESSION['post_code']           = $postCode;
                $_SESSION['prefecture']          = $prefecture;
                $_SESSION['address']             = $address;
                $_SESSION['email']               = $email;
                $_SESSION['hashed_pwd']          = $hashedPwd;
                $_SESSION['wareki']              = $wareki;
                $_SESSION['wareki_year']         = $warekiYear;
                $_SESSION['wareki_month']        = $warekiMonth;
                $_SESSION['wareki_date']         = $warekiDate;

                $_SESSION['full_address']  = '〒' . $postCode . ' ' . $prefecture . $address;
                $_SESSION['date_of_birth'] = "$wareki/$warekiYear/$warekiMonth/$warekiDate";

                redirectToPage('register_confirm.php');
            }
        }
        else
        {
            trigger_error('Unexpected error has occurred. We apologize for the inconvenience.');
        }
    }
}

$pageTitle = '会員登録';
include('./includes/header.html');
include('./views/register.html');
include('./includes/footer.html');

?>
