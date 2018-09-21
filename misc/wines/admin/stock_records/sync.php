<?php

// If the request is not https, terminate the script.
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off')
{
    exit('Please use the secure (encrypted) connection.');
}

// If the target CSV doesn't exist, terminate the script.
$curDirPath = dirname(__FILE__);
$csvFilePath = "$curDirPath/../../../stock_records.csv";
if (file_exists($csvFilePath) === FALSE)
{
    exit('Cannot find the data to sync.');
}

$disableSession = TRUE;
require_once("$curDirPath/../dbutils.php");
require_once("$curDirPath/../../../includes/config.inc.php");
require_once(MYSQL);


function formatCsv($filePath)
{
    $csvData  = '"barcode_number","stock_date","type","quantity","stock"' . "\n";
    $csvData .= file_get_contents($filePath);
    $csvData  = preg_replace('//', "\n", $csvData);
    $csvData  = preg_replace('//', "", $csvData);

    file_put_contents($filePath, $csvData);
}

function importStockRecordCsv($filePath)
{
    $file = fopen("$filePath", 'r');
    if ($file)
    { 
        $newline  = getLineBreakString();
        $rgstrRow = explode($newline, fread($file, filesize($filePath)));

        fclose($file);
    }

    // NOTE: Parsing fails if whitespace is inserted between CSV columns.
    $separator = "\",\"";

    // Exclude the first line (column names)
    $cLine = count($rgstrRow) - 1;

    // Trim extra double quotes from the first and last column name.
    $rgstrColName = explode($separator, $rgstrRow[0]);
    $rgstrColName[0] = ltrim($rgstrColName[0], "\"");
    $rgstrColName[count($rgstrColName) - 1] = rtrim($rgstrColName[count($rgstrColName) - 1], "\"");

    // Generate DB table name from the CSV file name.
    $iLastSlash = strrpos($filePath, "/");
    $fileName = substr($filePath, $iLastSlash + 1);
    $tableName = str_replace(".csv", "", $fileName);

    $rgstrQuery = array();

    for ($i = 1; $i < $cLine; ++$i)
    {
        $j = 0;
        $rgobjCol = array();

        $rgstrColValue = explode($separator, $rgstrRow[$i]);

        // Trim extra double quotes from the first and last column value.
        $rgstrColValue[0] = ltrim($rgstrColValue[0], "\"");
        $rgstrColValue[count($rgstrColValue) - 1] = rtrim($rgstrColValue[count($rgstrColValue) - 1], "\"");

        $strPoint = '';
        foreach ($rgstrColName as $strColName)
        {
            $colValue = $rgstrColValue[$j++];
            $colValue = str_replace("\"\"", "\"", $colValue);

            if ($strColName == 'stock_date')
            {
                $colValue = str_replace('.', '-', $colValue);
            }

            $rgobjCol[$strColName] = $colValue; 
        }

        if ($rgobjCol['stock'] < 0)
        {
            $rgobjCol['stock'] = 0;
        }

        $rgstrQuery[] = generateUpdateTableQuery($tableName, $rgobjCol, 0);
    }

    echo "Finished reading data from the CSV file.";

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
                echo("Failed Query: $strQuery");
            }
        }
    }

    $mysqli->close();

    return (!$fFailed);
}

formatCsv($csvFilePath);

if (importStockRecordCsv($csvFilePath))
{
    echo('<br /><br />Synced :)');

    // Send restocked alert.
    include_once("$curDirPath/../send_restocked_alert.php");
}
else
{
    echo('<br /><br />Failed to sync :(');
}
 
mysqli_close($dbc);
unlink($csvFilePath);

?>
