<?php

require_once('./defines.php');
require('../../../includes/config.inc.php');
require(MYSQL);

function generateWineListHtml($strContents)
{
    global $dbc;

    $html = '';

    $rgstrToken = explode('#;', $strContents);
    foreach ($rgstrToken as $strToken)
    {
        if (!empty($strToken))
        {
            $rgintToken = explode(':', $strToken);
            $code = $rgintToken[0];
            $qty  = $rgintToken[1];

            prepareNextQuery($dbc);

            $result = mysqli_query($dbc, "CALL shop.get_wine('$code')");
            if ($result !== FALSE)
            {
                $row   = mysqli_fetch_array($result, MYSQLI_ASSOC);
                $html .= ("#$code:&nbsp;&nbsp;" . $row['vintage'] . '&nbsp;' . $row['full_name'] . '&nbsp;(' . $row['producer'] . ")&nbsp;x&nbsp;$qty<br />");
            }
        }
    }

    return $html;
}

$pageTitle = '卸注文の管理';
include('../../../includes/header.html');

echo '
    <a href="http://sei-ya.jp/admin_home.html">&lt;&lt;&nbsp;Back to Admin Home</a>
    <br /><br />';

$result = mysqli_query($dbc, "CALL get_business_orders()");
if ($result !== FALSE)
{
    echo '
          <div id="popupPane" style="display:none;width:750px;padding:10px;background-color:black;color:white;">
          </div> 
          <table style="">
          <thead>
              <tr>
                  <td style="width:150px;">オーダーID</td>
                  <td style="width:200px;">名前</td>
                  <td style="width:100px;">配送希望日</td>
                  <td style="width:70px;">金額</td>
                  <td style="width:150px;">操作</td>
              </tr>
          </thead>
          <tbody>';

    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
    {
        $orderId      = 'ANYWAY_WS_' . str_pad($row['id'], 8, '0', STR_PAD_LEFT);
        $name         = $row['name'];
        $shippingFee  = $row['shipping_fee'];
        $wineTotal    = $row['wine_total'];
        $total        = floor(1.08 * ($wineTotal + $shippingFee));
        $paymentMethod = $row['payment_method'];
        $deliveryDate = $row['delivery_date'];
        $deliveryTime = $row['delivery_time'];
        $refrigerated = ($row['refrigerated'] == 1) ? 'Yes' : 'No';
        $email        = $row['customer_email'];
        $phone        = $row['phone'];
        $address      = '〒' . $row['post_code'] . '<br />' . $row['prefecture'] . $row['address'];
        $contents     = $row['contents'];
        $wineListHtml = generateWineListHtml($row['contents']);

        if ($paymentMethod == 1)
        {
            $paymentMethod = 'クレジットカード';
        }
        else if ($paymentMethod == 2)
        {
            $paymentMethod = '掛売り（銀行振込）';
        }


        $strTotal   = number_format($total);
        $detailHtml = "
            <input type=\"hidden\" value=\"$contents\" class=\"data_contents\" />
            <h1 class=\"data_orderId\">$orderId</h1>
            <h2 class=\"data_name\">$name</h2>
            Email:&nbsp;<span class=\"data_email\">$email</span><br />
            Phone:&nbsp;$phone
            <br /><br />
            [支払い方法]<br />
            $paymentMethod
            <br /><br />
            [合計金額]<br />
            小計:&nbsp;&nbsp;<span class=\"data_wine_total\">$wineTotal</span><br />
            送料:&nbsp;&nbsp;<span class=\"data_shipping_fee\">$shippingFee</span><br />
            $strTotal yen
            <br /><br />
            [配送先]<br />
            <span class=\"data_address\">$address</span>
            <br /><br />
            [配送希望時間]<br />
            <span class=\"data_delivery_date\">$deliveryDate</span>
            <span class=\"data_delivery_time\">$deliveryTime</span>
            <br /><br />
            [クール便]<br />
            <span class=\"data_refrigerated\">$refrigerated</span>
            <br /><br />
            [発注ワイン]<br />
            $wineListHtml";

        $buttonHtml = '';
        switch ($row['status'])
        {
        case '0':
            $buttonHtml = '<input type="button" class="confirmBtn" value="注文を確定する" />';
            break;
        case '2':
            $buttonHtml = '<input type="button" class="issueBtn" value="出庫済みにする" />';
            break;
        case '3':
            $buttonHtml = '<input type="button" class="shipBtn" value="発送済みにする" />';
            break;
        default:
            break;
        }
        $buttonHtml .= '&nbsp;&nbsp;<input type="button" class="cancelBtn" value="取消" />';

        echo '
            <tr id="' . $row['id'] . '">
                <td style="height:40px;">
                    <a href="#" class="orderDetailLnk">' . $orderId . '</a>
                </td>
                <td>' . $name. '</td>
                <td style="font-size:11px;text-align:center;">' . "$deliveryDate" . '</td>
                <td style="font-size:12px;text-align:right;">' . number_format($total) . ' yen</td>
                <td style="text-align:center;">' . $buttonHtml . '</td>
                <td style="display:none;" class="detailColumn">' . $detailHtml . '</td>
            </tr>';
    }

    echo '<tbody>
          </table>';

    mysqli_free_result($result);
}

include('../../../includes/footer.html');

?>

<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script> 
<script type="text/javascript">

$(document).ready(function()
{
    $('div.contents').on('mouseenter', 'a.orderDetailLnk', function(event)
    {
        var html = $(this).closest('tr').find('td.detailColumn').html();

        $('div#popupPane').html(html)
            .css('position', 'absolute')
            .show()
            .css('top', event.pageY);
    }).on('mouseleave', function(event)
    {
        $('div#popupPane').hide();
    });

    $('div.contents').on('click', 'input[type=button]', function()
    {
        var $parentTr = $(this).closest('tr'),
            orderId   = $parentTr.attr('id');

        if ($(this).hasClass('cancelBtn'))
        {
            $.ajax(
            {
                url:  './remove_business_order.php',
                type: 'POST',
                data:
                {
                    id: orderId
                },

                dataType: 'text',
                success: function(strResult)
                {
                    if (strResult === 'SUCCESS')
                    {
                        $parentTr.remove();
                    }
                    else
                    {
                        alert('Failed to remove the order [#' + orderId + ']');
                    }
                },

                error: function() {}
            });
        }
        else
        {
            var intStatus     = 0,
                $actionButton = $(this),
                buttonClass   = '',
                buttonText    = '';

                var $detailColumn   = $(this).closest('tr').find('td.detailColumn'),
                    argOrderId      = orderId,
                    //argOrderId      = $detailColumn.find('.data_orderId').html(),
                    argName         = $detailColumn.find('.data_name').html(),
                    argEmail        = $detailColumn.find('.data_email').html(),
                    argDeliveryDate = $detailColumn.find('.data_delivery_date').html(),
                    argDeliveryTime = $detailColumn.find('.data_delivery_time').html(),
                    argWineTotal    = $detailColumn.find('.data_wine_total').html(),
                    argShippingFee  = $detailColumn.find('.data_shipping_fee').html(),
                    argAddress      = $detailColumn.find('.data_address').html(),
                    argContents     = $detailColumn.find('input.data_contents').val();

            if ($actionButton.hasClass('confirmBtn'))
            {
                intStatus   = 2;
                buttonClass = 'issueBtn';
                buttonText  = '出庫済みにする';

                $.post('./send_confirmed_mail.php',
                    {
                        orderId:       argOrderId,
                        name:          argName,
                        email:         argEmail,
                        delivery_date: argDeliveryDate,
                        delivery_time: argDeliveryTime,
                        wine_total:    argWineTotal,
                        shipping_fee:  argShippingFee,
                        address:       argAddress,
                        contents:      argContents
                    },
                    function(data){});
            }
            else if ($actionButton.hasClass('issueBtn'))
            {
                intStatus   = 3;
                buttonClass = 'shipBtn';
                buttonText  = '発送済みにする';
            }
            else if ($actionButton.hasClass('shipBtn'))
            {
                var inputTrackingId = window.prompt('Please enter the tracking ID.', '0000-0000-0000');
                if (inputTrackingId != null)
                {
                    if ((/^([0-9-]{14})$/.test(inputTrackingId)) && (inputTrackingId != '0000-0000-0000'))
                    {
                        intStatus = 4;
                        $.post('./send_shipped_mail.php',
                        {
                            orderId:       argOrderId,
                            trackingId:    inputTrackingId,
                            name:          argName,
                            email:         argEmail,
                            delivery_date: argDeliveryDate,
                            delivery_time: argDeliveryTime,
                            address:       argAddress,
                            contents:      argContents
                        },
                        function(data){});
                    }
                    else
                    {
                       intStatus = 3;
                       alert('Invalid tracking ID!!');
                    }
                }
            }

            $.ajax(
            {
                url:  './set_business_order_status.php',
                type: 'POST',
                data:
                {
                    id: orderId,
                    status: intStatus
                },

                dataType: 'text',
                success: function(strResult)
                {
                    if (strResult === 'SUCCESS')
                    {
                        if ((buttonClass != '') && (buttonText != ''))
                        {
                            $actionButton.removeClass().addClass(buttonClass);
                            $actionButton.val(buttonText);
                        }
                        else
                        {
                            $actionButton.closest('tr').remove();
                        }
                    }
                    else
                    {
                        alert('Failed to chage the status of the order [#' + orderId + ']');
                    }
                },

                error: function() {}
            });
        }
    });
});

</script>

