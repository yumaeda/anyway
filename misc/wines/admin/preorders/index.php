<?php

$disableSession = TRUE;
require_once('../defines.php');
require_once('../../../includes/config.inc.php');
require_once(MYSQL);

echo '
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
        <title></title>
        <script type="text/javascript">

        document.createElement(\'aside\');

        </script>
        <style type="text/css">

        *
        {
            margin: 0;
            padding: 0;
        }

        h1
        {
            padding: 15px;
        }

        aside
        {
            padding: 15px;
        }

        .body
        {
            padding: 15px;
            font-size: 12px;
        }

        table
        {
            border-collapse: collapse;
            border: 1px solid rgb(222, 222, 222);
        }

        td
        {
            padding: 10px;
        }

        .qtyCol
        {
            text-align: center;
        }

        </style>
    </head>
    <body>
        <h1>Manage Preorders</h1>
        <aside>
            <form enctype="multipart/form-data" action="./sync.php" method="post" >
                <input type="file" name="file" id="file" required="required" />
                <br />
                <input type="submit" value="Create DB" />
            </form>
        </aside>
        <div class="body">
            <h2>Reserved Items</h2>
            <table>
                <thead style="background-color:snow;font-weight:bold;">
                    <tr>
                        <td>Vintage</td>
                        <td>Name</td>
                        <td>Producer</td>
                        <td>Size</td>
                        <td>Qty</td>
                    </tr>
                </thead>
                <tbody>
        ';

$result = mysqli_query($dbc, "CALL get_preordered_wines()");
if ($result !== FALSE)
{
    while ($row = mysqli_fetch_assoc($result))
    {
        $strVintage  = $row['vintage'];
        $strName     = $row['combined_name'];
        $strProducer = $row['producer'];
        $intCapacity = $row['capacity1'];
        $intQty      = $row['sold_qty'];

        echo "
                    <tr>
                        <td>$strVintage</td>
                        <td>$strName</td>
                        <td>$strProducer</td>
                        <td>$intCapacity ml</td>
                        <td class=\"qtyCol\">$intQty</td>
                    </tr>";
    }

    mysqli_free_result($result);
}

echo '
                </tbody>
            </table>
            <div style="margin-top:20px;">
                <a href="./reset.php">リセットする</a>
            </div>
        </div>
    </body>
</html>';

mysqli_close($dbc);

?>
