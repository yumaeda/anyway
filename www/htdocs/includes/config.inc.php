<?php

if (!defined('LIVE'))
{
    define('LIVE', false);
}

define('CONTACT_EMAIL', 'info@sei-ya.jp');
define('BASE_URI', '/usr/home/mw2pxfyv1a/www/');
define('BASE_URL', 'anyway-grapes.jp/');
define('MYSQL', BASE_URI . 'includes/mysql.inc.php');
define('UTIL', BASE_URI . 'includes/util.inc.php');
define('E_MAIL', BASE_URI . 'includes/text_mail.inc.php');
//define('E_MAIL', BASE_URI . 'includes/mail.inc.php');
define('VERITRANS', BASE_URI . 'includes/veritrans.inc.php');
define('PAYJP', BASE_URI . 'includes/payjp.inc.php');

define('BOX_BEGIN', '
    <div class="box alt">
        <div class="left-top-corner">
            <div class="right-top-corner">
                <div class="border-top"></div>
            </div>
        </div>
        <div class="border-left">
            <div class="border-right">
                <div class="inner">');

define('BOX_END', '
                </div>
            </div>
        </div>
        <div class="left-bot-corner">
            <div class="right-bot-corner">
                <div class="border-bot">
                </div>
            </div>
        </div>
    </div>');

function isSessionTimeout()
{
    $isTimedout = false;

    if (isset($_SESSION['LAST_ACTIVITY']))
    {
        $sinceLastActivity = time() - $_SESSION['LAST_ACTIVITY'];
        $timeoutPeriod     = 3 * 60 * 60; // 3 hours
        if ($sinceLastActivity > $timeoutPeriod)
        {
            $isTimedout = true;
        }
    }

    $_SESSION['LAST_ACTIVITY'] = time();

    return $isTimedout;
}


function startCartSession($dbc)
{
    session_start();
    $userId = session_id();

    if (isSessionTimeout())
    {
        // Regenerate the Session ID if the previous Session is already expired.
        if (session_regenerate_id(true))
        {
            mysqli_query($dbc, "CALL clear_preorder_cart('$userId')");
            prepareNextQuery($dbc);
            mysqli_query($dbc, "CALL clear_lucky_bag('$userId')");
            prepareNextQuery($dbc);
            mysqli_query($dbc, "CALL clear_cart('$userId')");
            session_unset();

            redirectToPage('timeout.php');
        }
        else
        {
            // Unexpected error.
        }
    }

    return $userId;
}


function clearCartSession()
{
    $_SESSION = array();
    session_destroy();
    unset($_COOKIE['SESSION']);
}


function getPostValue($key, $usedByQuery, &$errors)
{
    $value = '';

    if (isset($_POST[$key]))
    {
        $value = $_POST[$key];
        if ($usedByQuery)
        {
            $value = addslashes($value);
        }
    }

    if (empty($value))
    {
        $errors[$key] = "値を入力して下さい。";
    }

    return $value;
}


function redirectToPage($page)
{
    $returnUrl = '';
    if (isset($_REQUEST['returnUrl']))
    {
        $returnUrl = urlencode($_REQUEST['returnUrl']);
    }

    $urlQuerySeparator = '?';
    if (strpos($page, $urlQuerySeparator) !== FALSE)
    {
        $urlQuerySeparator = '&';
    }

    $location = 'https://' . BASE_URL . $page . $urlQuerySeparator . "returnUrl=$returnUrl";

    header("Location: $location");
    exit();
}

function customErrorHandler($errorCode, $message, $filename, $lineNumber, $params)
{
    $errorMessage = "An error occurred in script '$filename' on line: $lineNumber\n$message";
    $errorMessage .=
        '<pre>' .
            print_r(debug_backtrace(), 1) . '\n' .
            print_r($params, 1) .
        '</pre>\n';

    if (!LIVE)
    {
        echo '<div>' . nl2br($errorMessage) . '</div>';
    }
    else
    {
        error_log($errorMessage, 1, CONTACT_EMAIL, 'From:admin@anyway-grapes.jp');
        if ($errorCode != E_NOTICE)
        {
            echo '<div>A system error occurred.  We apologize for the inconvenience.</div>';
        }
    }

    return true;
}

set_error_handler('customErrorHandler');
