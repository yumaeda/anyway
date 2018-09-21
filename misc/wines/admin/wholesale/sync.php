<?php

$curDirPath = dirname(__FILE__);
$bodyHtml   = '';

// If the target CSV doesn't exist, terminate the script.
$csvFilePath = "$curDirPath/../../../wines.csv";
if (file_exists($csvFilePath) === FALSE)
{
    exit('Cannot find the data to sync.');
}

$disableSession = TRUE;
require_once("$curDirPath/defines.php");
require_once("$curDirPath/../dbutils.php");
require_once("$curDirPath/../../../includes/config.inc.php");
require_once(MYSQL);

function importWineCsv($filePath)
{
    global $bodyHtml;

    $file = fopen("$filePath", 'r');
    if ($file)
    { 
        $newline  = getLineBreakString();
        $rgstrRow = explode($newline, fread($file, filesize($filePath)));

        fclose($file);
    }

    // NOTE: Parsing fails if whitespace is inserted between CSV columns.
    $separator    = "\",\"";
    $rgstrQuery   = array();
    $tableName    = 'wholesale_wines';
    $rgstrColName = array('barcode_number', 'wholesale_price');

    // Exclude the first line (column names)
    $cLine = count($rgstrRow) - 1;
    for ($i = 1; $i < $cLine; ++$i)
    {
        $rgstrColValue = explode($separator, $rgstrRow[$i]);
        $iLastCol      = count($rgstrColValue) - 1;

        // Trim extra double quotes from the first and last column value.
        $rgstrColValue[0] = ltrim($rgstrColValue[0], "\"");
        $rgstrColValue[$iLastCol] = rtrim($rgstrColValue[$iLastCol], "\"");

        $rgobjCol = array();
        $rgobjCol[ $rgstrColName[0] ] = str_replace("\"\"", "\"", $rgstrColValue[1]);
        $rgobjCol[ $rgstrColName[1] ] = str_replace("\"\"", "\"", $rgstrColValue[$iLastCol]);

        $rgstrQuery[] = generateUpdateTableQuery($tableName, $rgobjCol, 0);
    }

    $bodyHtml .= 'Finished reading data from the CSV file.<br />';
    dropTable($tableName);
    createTable($tableName, $rgstrColName, 'id');

    $fFailed = FALSE;
    $mysqli  = connectToMySql();
    foreach ($rgstrQuery as $strQuery)
    {
        if ($strQuery)
        {
            if ($mysqli->query($strQuery) === FALSE)
            {
                $fFailed = TRUE;
                $bodyHtml .= ("Failed Query: $strQuery <br />");
            }
        }
    }

    $mysqli->close();
    return (!$fFailed);
}

function checkoutWine($dbc, $barcode, $qty)
{
    global $bodyHtml;

    mysqli_query($dbc, "CALL shop.checkout_wine('$barcode', '$qty', @fSuccess)");

    $fSuccess = 0;

    prepareNextQuery($dbc);
    $result = mysqli_query($dbc, 'SELECT @fSuccess');
    if (mysqli_num_rows($result) == 1)
    {
        list($fSuccess) = mysqli_fetch_array($result);
        mysqli_free_result($result);
    }

    if ($fSuccess == 1)
    {
        $bodyHtml .= "<br />&nbsp;&nbsp;&nbsp;&nbsp;$barcode [x$qty]";
    }
    else
    {
        $bodyHtml .= "<br />Failed to Checked out $barcode [x$qty]";
        $bodyHtml .= "<br />... Temporarily setting stock to 0 in order to prevent double-booking.";

        prepareNextQuery($dbc);
        mysqli_query($dbc, "CALL shop.checkout_all_wines('$barcode')");
    }
}

$fExportWines = FALSE;

// Do not need to format CSV since it is already formatted.
if (importWineCsv($csvFilePath))
{
    $result = mysqli_query($dbc, "CALL get_business_orders()");
    if ($result !== FALSE)
    {
        $strStatus = '';

        $bodyHtml .= '<br /><br />Checking-out reserved wines...<br />';
        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
        {
            $strStatus = $row['status'];
            if ((($strStatus === '0') || ($strStatus === '1') || ($strStatus === '2')) && !empty($row['contents']))
            {
                $rgstrItem = explode('#;', $row['contents']);
                $cItem = count($rgstrItem);
                for ($i = 0; $i < $cItem; ++$i)
                {
                    $rgstrToken = explode(':', $rgstrItem[$i]);
                    if (count($rgstrToken) === 2)
                    {
                        $barcode = $rgstrToken[0];
                        $qty     = $rgstrToken[1];

                        $bodyHtml .= "#$barcode x $qty <br />";
                        prepareNextQuery($dbc);
                        checkoutWine($dbc, $barcode, $qty);
                    }
                }
            }
        }

        mysqli_free_result($result);
    }

    $fExportWines = TRUE;
}
else
{
    $bodyHtml .= '<br /><br />Failed to sync :(';
}
 
mysqli_close($dbc);

echo '
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
        <meta http-equiv="refresh" content="0; url=http://anyway-grapes.jp/wines/admin/export_wines.php" />
    </head>
    <body>' . $bodyHtml . '</body>
</html>';

?>
