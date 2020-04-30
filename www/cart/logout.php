<?php

require('./includes/config.inc.php');
require_once(MYSQL);

$userId = startCartSession($dbc);
if (isset($_SESSION['user_id']))
{
    clearCartSession();
    setcookie(session_name(), '', time() - 300);
}

$strUserAgent = $_SERVER['HTTP_USER_AGENT'];
if(strpos($strUserAgent, 'Android') ||
   strpos($strUserAgent, 'iPhone') ||
   strpos($strUserAgent, 'iPod'))
{
    redirectToPage('./s/index.php');
}
else
{
    // Explicity redirect to home over HTTP.
    $location = 'http://anyway-grapes.jp/store/index.php';
    header("Location: $location");
    exit();
}

?>
