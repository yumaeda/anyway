<?php

require('./includes/config.inc.php');
require(MYSQL);

$inputErrors = array();
$userId      = startCartSession($dbc); 
$bodyHtml    = '';
if (isset($_SESSION['user_id']))
{
    $userEmail = $_SESSION['user_id'];

    if ($_SERVER['REQUEST_METHOD'] === 'GET')
    {
        $result = mysqli_query($dbc, "CALL get_purchased_wines_by_email('$userEmail')");
        if (($result !== FALSE) && (mysqli_num_rows($result) > 0))
        {
            while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
            {
                $code = $row['barcode_number'];
                $name = $row['vintage'] . ' ' . $row['full_name_jpn'] . ' / ' . $row['producer_jpn'];

                $bodyHtml .= '<a href="//anyway-grapes.jp/store/index.php?pc_view=1&submenu=wine_detail&id=9141&lang=ja" target="_blank">' . $name . '</a><br />';
            }
        }
    }

    $pageTitle = '購入履歴';
    include('./includes/header.html');
    include('./views/purchased_items.html');
    include('./includes/footer.html');
}

mysqli_close($dbc);

?>
