<?php

require_once('./includes/config.inc.php');
require(MYSQL);
require(UTIL);

// Enclose w/ try-catch block for debugging purpose.
try
{
    $intCartType = isset($_REQUEST['cart_type']) ? $_REQUEST['cart_type'] : 0;

    // Redirect to cart.php if 'SESSION' cookie is not set or invalid.
    if (!isset($_COOKIE['SESSION']) || (strlen($_COOKIE['SESSION']) !== 32))
    {
        redirectToPage("cart.php?cart_type=$intCartType");
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET')
    {
        session_id($_COOKIE['SESSION']);
    }

    $userId = startCartSession($dbc);

    // Reset the shipping fee.
    if (isset($_SESSION['shipping_fee']))
    {
        unset($_SESSION['shipping_fee']);
    }

    // Reset the cool fee.
    if (isset($_SESSION['cool_fee']))
    {
        unset($_SESSION['cool_fee']);
    }

    if ($intCartType == 0)
    {
        $result = mysqli_query($dbc, "CALL get_cart_contents('$userId')");
    }
    else if ($intCartType == 1)
    {
        $result = mysqli_query($dbc, "CALL get_lucky_bag_items('$userId')");
    }
    else if ($intCartType == 2)
    {
        $result = mysqli_query($dbc, "CALL get_preorder_cart_contents('$userId')");
    }

    if (!$result || (mysqli_num_rows($result) === 0))
    {
        // If the cart is empty, nothing to check out, so clear all the Session data and
        // redirect back to cart.php.
        clearCartSession();
        redirectToPage("cart.php?cart_type=$intCartType");
    }

    $inputErrors = array();
    $inputSrc    = 'SESSION';
    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        $inputSrc = 'POST';

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
            $inputErrors['wareki'] = 'お客様は未成年なので、酒類をご購入頂けません。';
        }

        $lastName      = getPostValue('last_name',      true, $inputErrors);
        $firstName     = getPostValue('first_name',     true, $inputErrors);
        $lastPhonetic  = getPostValue('last_phonetic',  true, $inputErrors);
        $firstPhonetic = getPostValue('first_phonetic', true, $inputErrors);
        $postCode      = getPostValue('post_code',      true, $inputErrors); // Need customized validation.
        $prefecture    = getPostValue('prefecture',     true, $inputErrors);
        $address       = getPostValue('address',        true, $inputErrors);
        $phone         = getPostValue('phone',          true, $inputErrors); // Need customized validation.

        $registerMailMagazine = 0;
        if (isset($_POST['mail_magazine']) && ($_POST['mail_magazine'] === '1'))
        {
            $registerMailMagazine = 1;
        }

        if (!$lastName || !$firstName)
        {
            $inputErrors['name'] = '名前（フルネーム）を入力してください。';
        }

        if (!$lastPhonetic || !$firstPhonetic)
        {
            $inputErrors['phonetic'] = 'ふりがな（フルネーム）を入力してください。';
        }

	if (!preg_match("/^\d{3}-?\d{4}$/", $postCode))
	{
	    $inputErrors['post_code'] = '正しい郵便番号を入力してください。';
	}

        if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) &&
            filter_var($_POST['email_confirm'], FILTER_VALIDATE_EMAIL) &&
            ($_POST['email'] === $_POST['email_confirm']))
        {
            $email = $_POST['email'];
        }
        else
        {
            $inputErrors['email'] = '正しいメールアドレスを入力してください。';
            $inputErrors['email_confirm'] = $inputErrors['email'];
        }

        if (empty($inputErrors))
        {
            $_SESSION['first_name']     = $firstName;
            $_SESSION['last_name']      = $lastName;
            $_SESSION['first_phonetic'] = $firstPhonetic;
            $_SESSION['last_phonetic']  = $lastPhonetic;
            $_SESSION['phone']          = $phone;
            $_SESSION['post_code']      = $postCode;
            $_SESSION['prefecture']     = $prefecture;
            $_SESSION['address']        = $address;

            $_SESSION['mail_magazine'] = $registerMailMagazine;
            $_SESSION['email']         = $email;

            $_SESSION['wareki']       = $wareki;
            $_SESSION['wareki_year']  = $warekiYear;
            $_SESSION['wareki_month'] = $warekiMonth;
            $_SESSION['wareki_date']  = $warekiDate;

            $_SESSION['cart_type'] = $intCartType;

            unset($_COOKIE['SESSION']);
            redirectToPage("delivery.php?cart_type=$intCartType");
        }
    }

    $pageTitle = 'お客様情報の入力｜anyway-grapes.jp';
    include('./includes/header.html');
    include('./views/checkout.html');
    include('./includes/footer.html');
}
catch (\Exception $e)
{
    sendErrorMail($e);
}

?>
