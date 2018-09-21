<?php

$disableSession = TRUE;
require_once('./defines.php');
require('../../../includes/config.inc.php');
require(MYSQL);

$bodyInnerHtml = '';
$result = mysqli_query($dbc, "CALL get_soldout_wines()");
if ($result !== FALSE)
{
    $bodyInnerHtml = '
        <h1>Soldout Wines</h1>
        <table>
            <tr style="background-color:rgb(80, 80, 80);color:white;">
                <td>Date</td>
                <td>Code</td>
                <td>Type</td>
                <td>Item Name</td>
                <td>Producer</td>
            </tr>
        ';

    while ($row = mysqli_fetch_assoc($result))
    {
        $strDate     = $row['stock_date'];
        $strBarcode  = $row['barcode_number'];
        $strType     = $row['type'];
        $strName     = $row['vintage'] . ' ' . $row['name'];
        $strProducer = $row['producer'];

        $bodyInnerHtml .= "
            <tr>
                <td>$strDate</td>
                <td>$strBarcode</td>
                <td>$strType</td>
                <td>$strName</td>
                <td>$strProducer</td>
            </tr>
        ";
    }

    $bodyInnerHtml .= '</table>';

    mysqli_free_result($result);
}
else
{
    echo 'There are no soldout wines.';
}


echo '
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
        <script type="text/javascript">

        document.createElement("header");

        </script>
        <style type="text/css">

        table
        {
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        td
        {
            padding: 10px;
            border: solid 1px rgb(222, 222, 222);
        }

        </style>
    </head>
    <body>
        <header>
            <a href="http://sei-ya.jp/admin_home.html">Admin Home</a>
        </header>
        <form action="./index.php" method="POST">' .
            $bodyInnerHtml . '
        </form>
    </body>
</html>
';

?>
