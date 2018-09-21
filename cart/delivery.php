<?php

/* COMMON CODE -Start- */

require_once('./includes/config.inc.php');
require(MYSQL);
require(UTIL);

// Enclose w/ try-catch block for debugging purpose.
try
{
    // common.php requires $userId global variable.
    $userId = startCartSession($dbc);
    require_once('./common.php');

    // Reset the In-Store pickup flag.
    if (isset($_SESSION['in_store_pickup']))
    {
        unset($_SESSION['in_store_pickup']);
    }

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

    prepareNextQuery($dbc);

    $intCartType = isset($_REQUEST['cart_type']) ? $_REQUEST['cart_type'] : 0;

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

    /* COMMON CODE -End- */

    if (!isset($_SESSION['first_name']) ||
        !isset($_SESSION['last_name']) ||
        !isset($_SESSION['first_phonetic']) ||
        !isset($_SESSION['last_phonetic']) ||
        !isset($_SESSION['email']) ||
        !isset($_SESSION['phone']) ||
        !isset($_SESSION['post_code']) ||
        !isset($_SESSION['prefecture']) ||
        !isset($_SESSION['address']))
    {
        redirectToPage("checkout.php?cart_type=$intCartType");
    }

    $inputErrors = array();
    $inputSrc    = 'SESSION';
    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        $inputSrc = 'POST';

        $deliveryOption = getPostValue('delivery_option', true, $inputErrors);
        if ($deliveryOption === 'ご自宅以外に配送')
        {
            $lastName      = getPostValue('delivery_last_name',      true, $inputErrors);
            $firstName     = getPostValue('delivery_first_name',     true, $inputErrors);
            $lastPhonetic  = getPostValue('delivery_last_phonetic',  true, $inputErrors);
            $firstPhonetic = getPostValue('delivery_first_phonetic', true, $inputErrors);
            $postCode      = getPostValue('delivery_post_code',      true, $inputErrors); // Need customized validation.
            $prefecture    = getPostValue('delivery_prefecture',     true, $inputErrors);
            $address       = getPostValue('delivery_address',        true, $inputErrors);
            $phone         = getPostValue('delivery_phone',          true, $inputErrors); // Need customized validation.
        }
        else if ($deliveryOption === 'ご自宅に配送')
        {
            $lastName      = $_SESSION['last_name'];
            $firstName     = $_SESSION['first_name'];
            $lastPhonetic  = $_SESSION['last_phonetic'];
            $firstPhonetic = $_SESSION['first_phonetic'];
            $postCode      = $_SESSION['post_code'];
            $prefecture    = $_SESSION['prefecture'];
            $address       = $_SESSION['address'];
            $phone         = $_SESSION['phone'];
        }
        else if ($deliveryOption === '店頭引き取り')
        {
            $lastName      = '店頭';
            $firstName     = '引き取り';
            $lastPhonetic  = ' ';
            $firstPhonetic = ' ';
            $postCode      = '156-0052';
            $prefecture    = '東京都';
            $address       = '世田谷区経堂 2-13-1-B1';
            $phone         = '03-6413-9737';
        }

        $deliveryCompany = getPostValue('delivery_company', true, $inputErrors);
        $deliveryDate    = getPostValue('delivery_date',    true, $inputErrors);
        $deliveryTime    = getPostValue('delivery_time',    true, $inputErrors);

        $useCooledPackage = 0;
        if (isset($_POST['refrigerated']) && ($_POST['refrigerated'] == 1))
        {
            $useCooledPackage = 1;
        }

        if (empty($lastName) || empty($firstName))
        {
            $inputErrors['name'] = '名前（フルネーム）を入力してください。';
        }

        if (empty($lastPhonetic) || empty($firstPhonetic))
        {
            $inputErrors['phonetic'] = 'ふりがな（フルネーム）を入力してください。';
        }

	if (!preg_match("/^\d{3}-?\d{4}$/", $postCode))
	{
	    $inputErrors['delivery_post_code'] = '正しい郵便番号を入力してください。';
	}

        if (!empty($_POST['comment']))
        {
            $_SESSION['comment'] = mysqli_real_escape_string($dbc, $_POST['comment']);
        }
        else
        {
            $_SESSION['comment'] = '';
        }

        if (empty($inputErrors))
        {
            $_SESSION['delivery_option']         = $deliveryOption;
            $_SESSION['delivery_first_name']     = $firstName;
            $_SESSION['delivery_last_name']      = $lastName;
            $_SESSION['delivery_first_phonetic'] = $firstPhonetic;
            $_SESSION['delivery_last_phonetic']  = $lastPhonetic;
            $_SESSION['delivery_phone']          = $phone;
            $_SESSION['delivery_post_code']      = $postCode;
            $_SESSION['delivery_prefecture']     = $prefecture;
            $_SESSION['delivery_address']        = $address;

            if ($useCooledPackage == 0)
            {
                unset($_SESSION['refrigerated']);
            }
            else
            {
                $_SESSION['refrigerated'] = $useCooledPackage;
            }

            $_SESSION['delivery_company'] = $deliveryCompany;
            $_SESSION['delivery_date']    = $deliveryDate;
            $_SESSION['delivery_time']    = $deliveryTime;

            prepareNextQuery($dbc);

            if ($intCartType == 0)
            {
                $tmpResult = mysqli_query($dbc, "CALL get_cart_wine_count('$userId')");
            }
            else if ($intCartType == 1)
            {
                $tmpResult = mysqli_query($dbc, "CALL get_lucky_bag_item_count('$userId')");
            }
            else if ($intCartType == 2)
            {
                $tmpResult = mysqli_query($dbc, "CALL get_preorder_cart_wine_count('$userId')");
            }

            if ($tmpResult !== FALSE)
            {
                list($totalQty) = mysqli_fetch_array($tmpResult);
                mysqli_free_result($tmpResult);

                if ($totalQty > 0)
                {
                    if ($cFortuneBox > 0)
                    {
                        // Assuming that fortune box contains 6-bottles.
                        $totalQty += (5 * $cFortuneBox);
                    }

                    $shippingFee = 0;
                    $intCoolFee  = $useCooledPackage ? getCoolFeeForYamato($totalQty) : 0;

                    if ($intCartType == 0)
                    {
                        $totalPrice = 0;
                        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
                        {
                            $totalPrice += getPrice($row) * $row['quantity'];
                        }

                        prepareNextQuery($dbc);

                        $tmpResult = mysqli_query($dbc, "CALL get_cart_wine_set_total('$userId')");
                        if ($tmpResult !== FALSE)
                        {
                            list($setPrice) = mysqli_fetch_array($tmpResult);
                            $totalPrice += $setPrice;
                            mysqli_free_result($tmpResult);
                        }

                        $shippingFee = getShippingFeeForYamato($totalQty, $totalPrice, $prefecture, $useCooledPackage, $intFreeShippingPrice, $deliveryDate);
                        if ($cWineSet > 0)
                        {
                            // If wine set is added to the cart, shipping fee for Yamato is applied.
                            $shippingFee = min($shippingFee, getShippingFeeForWineSet($cWineSet, $totalQty, $prefecture, $useCooledPackage, $deliveryDate));
                        }
                        else
                        {
                            if ($deliveryCompany == '佐川急便')
                            {
                                // Shipping cost for wine set is identical between Sagawa and Yamato.
                                $shippingFee = getShippingFee($totalPrice, $totalQty, $prefecture, $cWineSet, $intFreeShippingPrice);
                                $intCoolFee  = $useCooledPackage ? getCooledPackageFee($totalQty) : 0;
                            }
                        }
                    }
                    else if ($intCartType == 1)
                    {
                        $tmpResult = mysqli_query($dbc, "CALL get_lucky_bag_item_total('$userId')");
                        if ($tmpResult !== FALSE)
                        {
                            list($originalPrice, $totalPrice) = mysqli_fetch_array($tmpResult);
                            mysqli_free_result($tmpResult);
                        }

                        $shippingFee = getHappyBoxShippingFee($totalPrice, $totalQty, $prefecture);
                    }
                    else if ($intCartType == 2)
                    {
                        $totalPrice = 0;
                        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
                        {
                            $totalPrice += getPrice($row) * $row['quantity'];
                        }

                        $shippingFee = getShippingFeeForYamato($totalQty, $totalPrice, $prefecture, $useCooledPackage, $intFreeShippingPrice, $deliveryDate);
                        if ($deliveryCompany == '佐川急便')
                        {
                            // Shipping cost for wine set is identical between Sagawa and Yamato.
                            $shippingFee = getShippingFee($totalPrice, $totalQty, $prefecture, 0, $intFreeShippingPrice);
                            $intCoolFee  = $useCooledPackage ? getCooledPackageFee($totalQty) : 0;
                        }
                    }

                    $email    = mysqli_real_escape_string($dbc, $_SESSION['email']);
                    $name     = mysqli_real_escape_string($dbc, "$lastName$firstName");
                    $phonetic = mysqli_real_escape_string($dbc, "$lastPhonetic$firstPhonetic");
                    $comment  = $_SESSION['comment'];

                    prepareNextQuery($dbc);

                    // Set the shipping fee to 0 if the customer want to pick up the purchased items.
                    if ($deliveryOption === '店頭引き取り')
                    {
                        $deliveryCompany             = '';
                        $shippingFee                 = 0;
                        $intCoolFee                  = 0;
                        $useCooledPackage            = 0;
                        $_SESSION['in_store_pickup'] = 1;
                    }

                    if ($cFortuneBox > 0)
                    {
                        // Only use Yamato for Fortune Box.
                        // Set the cool fee to 0 if the number of 6-bottle fortune box is greater than the total number of shipping boxes.
                        $cTotalBox = getBoxCountForYamato($totalQty, $useCooledPackage);
                        if ($cFortuneBox >= $cTotalBox)
                        {
                            $intCoolFee  = 0;
                        }
                        else
                        {
                            $intCoolFee = 300 * ($cTotalBox - $cFortuneBox);
                        }
                    }

                    $tmpResult = mysqli_query($dbc, "CALL add_shipping('$email', '$name', '$phonetic', '$postCode', '$prefecture', '$address', '$phone', '$deliveryCompany',
                                                                    '$deliveryDate', '$deliveryTime', $useCooledPackage, '$comment', $shippingFee + $intCoolFee, @shippingId)");
                    if ($tmpResult != FALSE)
                    {
                        prepareNextQuery($dbc);
                        $tmpResult = mysqli_query($dbc, 'SELECT @shippingId');
                        if (($tmpResult !== FALSE) && (mysqli_num_rows($tmpResult) == 1))
                        {
                            // Unset 'SESSION' cookie, which will be not used anymore.
                            list($_SESSION['shipping_id']) = mysqli_fetch_array($tmpResult);
                            mysqli_free_result($tmpResult);

                            $_SESSION['shipping_fee'] = $shippingFee;
                            $_SESSION['cool_fee']     = $intCoolFee;

                            redirectToPage("confirmation_new.php?cart_type=$intCartType");
                        }
                    }
                }
            }

            trigger_error('Your order could not be processed due to a system error.  We apologize for the inconvenience.');
        }
        else
        {
            // Do not process user input, since there are still so errors.
        }
    }
    else
    {
        if (isset($_SESSION['delivery_option']) && ($_SESSION['delivery_option'] !== 'ご自宅以外に配送'))
        {
            unset($_SESSION['delivery_first_name']);
            unset($_SESSION['delivery_last_name']);
            unset($_SESSION['delivery_first_phonetic']);
            unset($_SESSION['delivery_last_phonetic']);
            unset($_SESSION['delivery_phone']);
            unset($_SESSION['delivery_post_code']);
            unset($_SESSION['delivery_prefecture']);
            unset($_SESSION['delivery_address']);
        }
    }

    $pageTitle = 'お客様情報の入力｜anyway-grapes.jp';
    include('./includes/header.html');
    include('./views/delivery.html');
    include('./includes/footer.html');

    echo '
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script> 
<script type="text/javascript" src="//jpostal-1006.appspot.com/jquery.jpostal.js"></script>
<script type="text/javascript">

function generateDeliveryTimeSelectInnerHtml(strDeliveryCompany)
{
    var html  =
        "<option value=\"指定なし\">指定なし</option>" +
        "<option value=\"午前中（8:00〜12:00）\">午前中（8:00〜12:00）</option>";

    if (strDeliveryCompany == "佐川急便")
    {
        html += "<option value=\"12:00 ～ 14:00\">12:00 ～ 14:00</option>";
    }

    html += "<option value=\"14:00 ～ 16:00\">14:00 ～ 16:00</option>";
    html += "<option value=\"16:00 ～ 18:00\">16:00 ～ 18:00</option>";
    html += "<option value=\"18:00 ～ 20:00\">18:00 ～ 20:00</option>";
    html += "<option value=\"19:00 ～ 21:00\">19:00 ～ 21:00</option>";

    return html;
}

function generatePreorderDeliveryDateSelectInnerHtml(strDeliveryCompany)
{
    var html  =
        "<option value=\"' . date('Y年m月d日', strtotime("+7 days")) . '\">' . date('Y年m月d日', strtotime("+7 days")) . '</option>" +
        "<option value=\"' . date('Y年m月d日', strtotime("+8 days")) . '\">' . date('Y年m月d日', strtotime("+8 days")) . '</option>" +
        "<option value=\"' . date('Y年m月d日', strtotime("+9 days")) . '\">' . date('Y年m月d日', strtotime("+9 days")) . '</option>" +
        "<option value=\"' . date('Y年m月d日', strtotime("+10 days")) . '\">' . date('Y年m月d日', strtotime("+10 days")) . '</option>" +
        "<option value=\"' . date('Y年m月d日', strtotime("+11 days")) . '\">' . date('Y年m月d日', strtotime("+11 days")) . '</option>" +
        "<option value=\"' . date('Y年m月d日', strtotime("+12 days")) . '\">' . date('Y年m月d日', strtotime("+12 days")) . '</option>" +
        "<option value=\"' . date('Y年m月d日', strtotime("+13 days")) . '\">' . date('Y年m月d日', strtotime("+13 days")) . '</option>" +
        "<option value=\"' . date('Y年m月d日', strtotime("+14 days")) . '\">' . date('Y年m月d日', strtotime("+14 days")) . '</option>";

    return html;
}

function generateDeliveryDateSelectInnerHtml(strDeliveryCompany)
{
    var html  =
        "<option value=\"指定なし\">指定なし</option>" +
        "<option value=\"' . date('Y年m月d日', strtotime("+3 days")) . '\">' . date('Y年m月d日', strtotime("+3 days")) . '</option>" +
        "<option value=\"' . date('Y年m月d日', strtotime("+4 days")) . '\">' . date('Y年m月d日', strtotime("+4 days")) . '</option>" +
        "<option value=\"' . date('Y年m月d日', strtotime("+5 days")) . '\">' . date('Y年m月d日', strtotime("+5 days")) . '</option>" +
        "<option value=\"' . date('Y年m月d日', strtotime("+6 days")) . '\">' . date('Y年m月d日', strtotime("+6 days")) . '</option>" +
        "<option value=\"' . date('Y年m月d日', strtotime("+7 days")) . '\">' . date('Y年m月d日', strtotime("+7 days")) . '</option>" +
        "<option value=\"' . date('Y年m月d日', strtotime("+8 days")) . '\">' . date('Y年m月d日', strtotime("+8 days")) . '</option>" +
        "<option value=\"' . date('Y年m月d日', strtotime("+9 days")) . '\">' . date('Y年m月d日', strtotime("+9 days")) . '</option>" +
        "<option value=\"' . date('Y年m月d日', strtotime("+10 days")) . '\">' . date('Y年m月d日', strtotime("+10 days")) . '</option>";

    return html;
}

$(window).ready(function()
{
    $("input#delivery_post_code").jpostal({
	postcode:
	[
	    "#delivery_post_code"
	],
	address:
	{
	    "#delivery_prefecture": "%3",
	    "#delivery_address": "%4%5"
	}
    });
});

$(document).ready(function()
{
    $("form").on("change", "select[name=delivery_company]", function()
    {';

    if ($intCartType == 2)
    {
        echo '
        $("select[name=delivery_date]").html(generatePreorderDeliveryDateSelectInnerHtml($(this).val()));';
    }
    else
    {
        echo '
        $("select[name=delivery_date]").html(generateDeliveryDateSelectInnerHtml($(this).val()));';
    }

    echo '
        $("select[name=delivery_time]").html(generateDeliveryTimeSelectInnerHtml($(this).val()));
        $("input#refrigerated").show();
    });

    $("form").on("change", "select[name=delivery_date]", function()
    {
        var $this              = $(this),
            strDeliveryCompany = $("select[name=delivery_company]").val();

        $("select[name=delivery_time]").html(generateDeliveryTimeSelectInnerHtml(strDeliveryCompany));
        $("input#refrigerated").show();
    });
});

</script>
';
}
catch (\Exception $e)
{
    sendErrorMail($e);
}

?>
