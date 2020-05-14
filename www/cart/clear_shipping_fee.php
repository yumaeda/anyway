<?php
require_once('./includes/config.inc.php');
require(MYSQL);

$userId = '';
if (!isset($_COOKIE['SESSION']) || (strlen($_COOKIE['SESSION']) !== 32)) {
    $userId = openssl_random_pseudo_bytes(16);
    $userId = bin2hex($userId);

    // Set cookie, which expires in 24 hours.
    $expireDate = time() + (1 * 24 * 60 * 60);
    setcookie('SESSION', $userId, $expireDate, '/', 'anyway-grapes.jp');
} else {
    session_id($_COOKIE['SESSION']);
    $userId = startCartSession($dbc);
}

// Clears the shipping fee.
if (isset($_SESSION['shipping_fee'])) {
    unset($_SESSION['shipping_fee']);
}

// Clears the cool fee.
if (isset($_SESSION['cool_fee'])) {
    unset($_SESSION['cool_fee']);
}