<?php

// Delete the default Session cookie first.
setcookie(session_name(), '', time() - 3600, '/');

require_once('./clear_shipping_fee.php');

$fDisableEdit = isset($_SESSION['disable_cart_edit']);
$intCartType  = isset($_REQUEST['cart_type']) ? $_REQUEST['cart_type'] : 0;
if ($intCartType == 0)
{
    $page_title = 'Anyway-Grapes.JP: 買い物かご';
}
else if ($intCartType == 1)
{
    $page_title = 'Anyway-Grapes.JP: 自分で選ぶ福箱';
}
else if ($intCartType == 2)
{
    $page_title = 'Anyway-Grapes.JP: 買い物かご（予約販売）';
}

include('./includes/header.html');

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    if (isset($_POST['action']))
    {
        $action = mysqli_real_escape_string($dbc, $_POST['action']);

        if ($action === 'clear')
        {
            if ($intCartType == 0)
            {
                mysqli_query($dbc, "CALL clear_cart('$userId')");
            }
            else if ($intCartType == 1)
            {
                mysqli_query($dbc, "CALL clear_lucky_bag('$userId')");
            }
            else if ($intCartType == 2)
            {
                mysqli_query($dbc, "CALL clear_preorder_cart('$userId')");
            }

            unset($_SESSION['disable_cart_edit']);
        }
        else if (isset($_POST['pid']))
        {
            $productId = mysqli_real_escape_string($dbc, $_POST['pid']);

            if ($action === 'add')
            {
                $filteredQty = 1;
                if (isset($_POST['qty']))
                {
                    $qty = $_POST['qty'];
                    $filteredQty = (filter_var($qty, FILTER_VALIDATE_INT, array('min_range' => 1)) !== false) ? $qty : 1;
                }

                if ($intCartType == 0)
                {
                    $memberType = (isset($_SESSION['user_id']) && isset($_SESSION['user_name'])) ? 1 : 0;
                    mysqli_query($dbc, "CALL add_to_cart('$userId', $productId, $filteredQty, $memberType)");
                }
                else if ($intCartType == 1)
                {
                    mysqli_query($dbc, "CALL add_to_lucky_bag('$userId', $productId, $filteredQty)");
                }
                else if ($intCartType == 2)
                {
                    mysqli_query($dbc, "CALL add_to_preorder_cart('$userId', $productId, $filteredQty)");
                }
            }
            elseif (($action === 'remove') && !$fDisableEdit)
            {
                if ($intCartType == 0)
                {
                    mysqli_query($dbc, "CALL remove_from_cart('$userId', $productId)");
                }
                else if ($intCartType == 1)
                {
                    mysqli_query($dbc, "CALL remove_from_lucky_bag('$userId', $productId)");
                }
                else if ($intCartType == 2)
                {
                    mysqli_query($dbc, "CALL remove_from_preorder_cart('$userId', $productId)");
                }
            }
            
            // For add / remove operation, terminate right after DB operation.
            exit();
        }
    }
    elseif (isset($_POST['quantity']) && !$fDisableEdit)
    {
        foreach ($_POST['quantity'] as $barcode => $qty)
        {
            if (isset($barcode))
            {
                $filteredQty = (filter_var($qty, FILTER_VALIDATE_INT, array('min_range' => 0)) !== false) ? $qty : 1;
                if ($intCartType == 0)
                {
                    mysqli_query($dbc, "CALL update_cart('$userId', $barcode, $filteredQty)");
                }
                else if ($intCartType == 1)
                {
                    mysqli_query($dbc, "CALL update_lucky_bag('$userId', $barcode, $filteredQty)");
                }
                else if ($intCartType == 2)
                {
                    mysqli_query($dbc, "CALL update_preorder_cart('$userId', $barcode, $filteredQty)");
                }
            }
        }
    }
}


if ($intCartType == 0)
{
    $result = mysqli_query($dbc, "CALL get_cart_contents('$userId')");
    if ($result !== FALSE)
    {
        if (mysqli_num_rows($result) > 0)
        {
            include('./views/cart.html');
        }
        else
        {
            include('./views/emptycart.html');
        }

        mysqli_free_result($result);
    }
}
else if ($intCartType == 1)
{
    $result = mysqli_query($dbc, "CALL get_lucky_bag_items('$userId')");
    if ($result !== FALSE)
    {
        if (mysqli_num_rows($result) > 0)
        {
            include('./views/happy_box.html');
        }
        else
        {
            include('./views/empty_happy_box.html');
        }

        mysqli_free_result($result);
    }
}
else if ($intCartType == 2)
{
    $result = mysqli_query($dbc, "CALL get_preorder_cart_contents('$userId')");
    if ($result !== FALSE)
    {
        if (mysqli_num_rows($result) > 0)
        {
            include('./views/preorder_cart.html');
        }
        else
        {
            include('./views/emptycart.html');
        }

        mysqli_free_result($result);
    }
}

include('./includes/footer.html');

?>

<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script type="text/javascript">

$(document).ready(function()
{
    var cartType = <?php echo json_encode($intCartType); ?>; 

    $('td.operationCol').on('click', 'a.removeWineLnk', function()
    {
        var $parentTr = $(this).closest('tr'),
            productId = this.id;

        $.ajax(
        {
            url:  './cart.php',
            type: 'POST',
            data:
            {
                action:    'remove',
                pid:       productId,
                cart_type: cartType
            },

            success: function(strResponse)
            {
                location.reload(true);
            },

            error: function()
            {
                console.error(productId + ' cannot be removed from the cart.');
            }
        });

        return false;
    });

    $('tfoot').on('click', 'a#clearCartLnk', function()
    {
        $.ajax(
        {
            url:  './cart.php',
            type: 'POST',
            data:
            {
                action:    'clear',
                cart_type: cartType
            },

            success: function(strResponse)
            {
                location.reload(true);
            },

            error: function()
            {
                console.error('Failed to clear the cart.');
            }
        });

        return false;
    });
});

</script>

