<?php

// Global Constants
define('INVALID_ITEM_PRICE', 999999999);
define('SYSTEM_ALERT_EMAIL', 'sysadm@anyway-grapes.jp');

// Make sure PHP Session is enabled.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function isAuthenticated()
{
    // Use empty() to check the Session variables.
    // false, 0, and '0' are regarded as invalid values here.
    return (!empty($_SESSION['user_id']) && !empty($_SESSION['user_name']));
}

// Global Variables
$cGoods               = 0;
$cWineSet             = 0;
$cFortuneBox          = 0;
$intFreeShippingPrice = isAuthenticated() ? 20000 : 15000;

// Initialise $cGoods.
prepareNextQuery($dbc);
$tmpResult = mysqli_query($dbc, "CALL get_cart_goods_count('$userId')");
if ($tmpResult !== FALSE)
{
    list($cGoods) = mysqli_fetch_array($tmpResult);
    mysqli_free_result($tmpResult);
}

// Initialise $cWineSet.
prepareNextQuery($dbc);
$tmpResult = mysqli_query($dbc, "CALL get_cart_set_count('$userId')");
if ($tmpResult !== FALSE)
{
    list($cWineSet) = mysqli_fetch_array($tmpResult);
    mysqli_free_result($tmpResult);
}

// Initialise $cFortuneBox.
prepareNextQuery($dbc);
$tmpResult = mysqli_query($dbc, "CALL get_cart_fortune_box_count('$userId')");
if ($tmpResult !== FALSE)
{
    list($cFortuneBox) = mysqli_fetch_array($tmpResult);
    mysqli_free_result($tmpResult);
}


function getDeliveryBoxCount($intQty)
{
    $intBox = intval($intQty / 12, 10);
    if (($intQty % 12) > 0)
    {
        ++$intBox;
    }

    return $intBox;
}

function getFreeBoxCount($total)
{
    global $intFreeShippingPrice;

    return floor($total / $intFreeShippingPrice);
}

/**
 * Gets the price of the specified wine.
 *
 * Member price is returned if the caller is authenticated and
 * $wine contains 'member_price' entry.
 *
 * If $wine doesn't contain 'price' entry, INVALID_ITEM_PRICE is
 * returned and system alert mail is sent.
 *
 * @param array $wine
 * @return int
 */
function getPrice($wine)
{
    $price = INVALID_ITEM_PRICE;

    if (empty($wine['price'])) {
        error_log('[#' . $wine['barcode_number'] . ']: Price is not set.', 1, SYSTEM_ALERT_EMAIL);
    }
    else {
        // empty() returns true when the value is 0 also.
        $price = (isAuthenticated() && !empty($wine['member_price'])) ?
            $wine['member_price'] :
            $wine['price'];
    }

    return $price;
}

function generatePriceHtml($sqlRow)
{
    return (number_format(getPrice($sqlRow)) . ' yen');
}

