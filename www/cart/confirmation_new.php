<?php

/* COMMON CODE -Start- */

require_once('./includes/config.inc.php');
require_once(MYSQL);

$userId      = startCartSession($dbc);
$intCartType = isset($_REQUEST['cart_type']) ? $_REQUEST['cart_type'] : 0;

// Redirect to final.php if the order is already processed.
if (isset($_SESSION['paid_order']))
{
    redirectToPage("final.php?cart_type=$intCartType");
}

// Redirect to checkout.php if the shipping information is not stored.
if (!isset($_SESSION['shipping_fee']) ||
    !isset($_SESSION['shipping_id']))
{
    redirectToPage("checkout.php?cart_type=$intCartType");
}

/* COMMON CODE -End- */

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    // redirectToPage("payment_new.php?cart_type=$intCartType");
    redirectToPage("payment_payjp.php?cart_type=$intCartType");
}

$pageTitle = '注文内容の確認｜anyway-grapes.jp';
include('./includes/header.html');

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

if ($result !== FALSE)
{
    if (mysqli_num_rows($result) > 0)
    {
        include('./views/confirmation_new.html');
    }
    else
    {
        if ($intCartType == 0)
        {
            include('./views/emptycart.html');
        }
        else if ($intCartType == 1)
        {
            include('./views/empty_happy_box.html');
        }
        else if ($intCartType == 2)
        {
            include('./views/emptycart.html');
        }
    }

    mysqli_free_result($result);
}

include('./includes/footer.html');

?>
