<?php

// If the request is not https, terminate the script.
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off')
{
    exit('Please use the secure (encrypted) connection.');
}

$curDirPath = dirname(__FILE__);

// If DB sync is already running, terminate the script.
$dbSyncStatusFilePath = "$curDirPath/../../syncStatus.txt";
$dbSyncStatus         = file_get_contents($dbSyncStatusFilePath);
if ($dbSyncStatus == '1')
{
    header('Content-type: text/html; charset=utf-8');
    exit('只今同期中です。5分程お待ちください。');
}
else
{
    file_put_contents($dbSyncStatusFilePath, '1');
}

// If the target CSV doesn't exist, terminate the script.
$csvFilePath = "$curDirPath/../../wines.csv";
if (file_exists($csvFilePath) === FALSE)
{
    exit('Cannot find the data to sync.');
}
else
{
    try {
        copy($csvFilePath, "$curDirPath/../../backup.csv");
    }
    catch (Exception $ex) {
        // Do nothing.
    }
}

$disableSession = TRUE;
require_once("$curDirPath/dbutils.php");
require_once("$curDirPath/../../includes/config.inc.php");
require_once(MYSQL);


function formatCsv($filePath)
{
    $csvData =
        '"glass_price","barcode_number","price","member_price","cepage","store_price","rating","rating_jpn","cultivation_method","stock","importer","type","country","producer","producer_jpn","vintage","village","village_jpn","district","district_jpn","region","region_jpn","apply","availability","etc","comment","name","name_jpn","point","catch_copy","capacity1","capacity2","capacity3","capacity4","wholesale_price"' . "\n" .
        '"","100","300","300","300","","300","","","","1000","","","","","","","","","","","","","S","Online","","","Gift Box (1 Bottle)","ギフト・ボックス（1本用）","","","","","","",""' . "\n" .
        '"","101","500","500","500","","500","","","","1000","","","","","","","","","","","","","S","Online","","","Gift Box (2 Bottles)","ギフト・ボックス（2本用）","","","","","","",""' . "\n";

    $csvData .= file_get_contents($filePath);
    $csvData  = preg_replace('//', "\n", $csvData);
    $csvData  = preg_replace('//', "", $csvData);

    file_put_contents($filePath, $csvData);
}

function appendToken($strSrc, $strToken, $strDelimiter)
{
    $strResult = $strSrc;

    if (!empty($strToken))
    {
        if (!empty($strResult))
        {
            $strResult .= $strDelimiter;
        }

        $strResult .= $strToken;
    }

    return $strResult;
}

function replaceWineScoreAbbreviation($txt)
{
    $rootUrl = '//anyway-grapes.jp/critics/';
    $wineScoreTable = array(
        'WA'         => '○○',
        'WM'         => '<a target="_blank" href="'. $rootUrl . 'wm/index.php">ワインマグ</a>：',
        'WS'         => '<a target="_blank" href="'. $rootUrl . 'ws/index.php">ワイン・スペクテイター</a>：',
        'DECANTER'   => '<a target="_blank" href="'. $rootUrl . 'decanter/index.php">デキャンタ</a>：',
        'G.H.'       => '<a target="_blank" href="'. $rootUrl . 'gh/index.php">ギド・アシェット</a>：',
        'WE'         => '<a target="_blank" href="'. $rootUrl . 'we/index.php">ワイン・エンスージアスト</a>：',
        'ST'         => '<a target="_blank" href="'. $rootUrl . 'vinous/index.php">ステファン・タンザー：</a>：',
        'JR'         => '<a target="_blank" href="'. $rootUrl . 'jr/index.php">ジャンシス・ロビンソン</a>：',
        'B&D'        => '<a target="_blank" href="'. $rootUrl . 'bd/index.php">ベタンヌ&ドゥソーヴ</a>：',
        'VINOUS'     => '<a target="_blank" href="'. $rootUrl . 'vinous/index.php">ヴィノス(アントニオ・ガローニ)</a>：',
        'AM'         => '<a target="_blank" href="'. $rootUrl . 'am/index.php">アラン・メドゥ</a>：',
        'RVF'        => '<a target="_blank" href="'. $rootUrl . 'rvf/index.php">ルヴュ・ド・ヴァン・ド・フランス</a>：',
        'TA'         => '<a target="_blank" href="'. $rootUrl . 'ta/index.php">ティム・アトキン</a>：',
        'VINUM'      => '<a target="_blank" href="'. $rootUrl . 'vinum/index.php">ヴィナム・ワイン・マガジン</a>：',
        'G&M'        => '<a target="_blank" href="'. $rootUrl . 'gm/index.php">ゴー&ミヨ</a>：',
        'BIBENDA'    => '<a target="_blank" href="'. $rootUrl . 'bibenda/index.php">ビベンダ</a>：',
        'ESPRESSO'   => '<a target="_blank" href="'. $rootUrl . 'espresso/index.php">エスプレッソ</a>：',
        'G&G'        => '<a target="_blank" href="'. $rootUrl . 'gg/index.php">ジルベール&ガイヤール</a>：',
        'JG'         => '<a target="_blank" href="'. $rootUrl . 'jg/index.php">ジェイミー・グッド</a>：',
        'JH'         => '<a target="_blank" href="'. $rootUrl . 'jh/index.php">ジェームズ・ハリデー</a>：',
        'Platter\'s' => '<a target="_blank" href="'. $rootUrl . 'platters/index.php">プラッターズ</a>：',
        'LV'         => '<a target="_blank" href="'. $rootUrl . 'lv/index.php">ヴェロネッリ</a>：',
        'GRVI'       => '<a target="_blank" href="'. $rootUrl . 'grvi/index.php">ガンベロロッソ・ヴィニ・ディタリア</a>：',
        'JS'         => '<a target="_blank" href="'. $rootUrl . 'js/index.php">ジェームス・サックリング</a>：',
        'JL'         => '<a target="_blank" href="'. $rootUrl . 'jl/index.php">ジェフ・リーヴ</a>：');

    return str_replace(array_keys($wineScoreTable), array_values($wineScoreTable), $txt);
}

function generateWineName($strType, $strCountry, $strRegion, $strVillage, $strRating,  $strName, $strProducer)
{
    $wineName  = '';
    $delimiter = ' ';
    $strNA     = '-----------------------';

    $strVillage = str_replace($strNA, '', $strVillage);
    $strRegion  = str_replace($strNA, '', $strRegion);

    if ($strCountry === 'South Africa')
    {
        $wineName = appendToken($wineName, $strRating, $delimiter);
        $wineName = appendToken($wineName, $strVillage, $delimiter);
        $wineName = appendToken($wineName, $strName, $delimiter);
    }
    else if ($strCountry === 'Germany')
    {
        $wineName = appendToken($wineName, $strVillage, $delimiter);
        $wineName = appendToken($wineName, $strName, $delimiter);
    }
    else if (($strCountry === 'Italy') ||
             ($strCountry === 'Spain'))
    {
        $wineName = appendToken($wineName, $strRating, $delimiter);
        $wineName = appendToken($wineName, $strVillage, $delimiter);
        $wineName = appendToken($wineName, $strName, $delimiter);
    }
    else if ($strRegion === 'Bordeaux')
    {
        $wineName = appendToken($wineName, $strName, $delimiter);
    }
    else if (($strRegion === 'Champagne') && (strpos($strType, 'Champagne') === 0))
    {
        $wineName = appendToken($wineName, $strProducer, $delimiter);
        $wineName = appendToken($wineName, $strName, $delimiter);
        $wineName = appendToken($wineName, $strRating, $delimiter);
    }
    else
    {
        if (($strRegion === 'Bourgogne') &&
            ($strRating === 'Grand Cru') &&
            ($strVillage !== 'Chablis'))
        {
            $wineName = appendToken($wineName, $strName, $delimiter);
            $wineName = appendToken($wineName, $strRating, $delimiter);
        }
        else
        {
            $wineName = appendToken($wineName, $strVillage, $delimiter);
            $wineName = appendToken($wineName, $strRating, $delimiter);
            $wineName = appendToken($wineName, $strName, $delimiter);
        }
    }

    return $wineName;
}

function generateJpnWineName($strType, $strCountry, $strRegion, $strVillage, $strRating, $strJpnRating, $strName, $strProducer)
{
    $wineName  = '';
    $delimiter = '・';
    $strNA     = '-----------------------';

    $strVillage = str_replace($strNA, '', $strVillage);
    $strRegion  = str_replace($strNA, '', $strRegion);

    if ($strCountry === 'South Africa')
    {
        $wineName = appendToken($wineName, $strJpnRating, ' ');
        $wineName = appendToken($wineName, $strVillage, $delimiter);
        $wineName = appendToken($wineName, $strName, $delimiter);
    }
    else if ($strCountry === 'Germany')
    {
        $wineName = appendToken($wineName, $strVillage, $delimiter);
        $wineName = appendToken($wineName, $strName, $delimiter);
    }
    else if (($strCountry === 'Italy') ||
             ($strCountry === 'Spain'))
    {
        $wineName = appendToken($wineName, $strRating, $delimiter);
        $wineName = appendToken($wineName, $strVillage, '');
        $wineName = appendToken($wineName, $strName, $delimiter);
    }
    else if ($strRegion === 'Bordeaux')
    {
        $wineName = appendToken($wineName, $strName, $delimiter);
    }
    else if (($strRegion === 'Champagne') && (strpos($strType, 'Champagne') === 0))
    {
        $wineName = appendToken($wineName, $strProducer, $delimiter);
        $wineName = appendToken($wineName, $strName, $delimiter);
        $wineName = appendToken($wineName, $strJpnRating, $delimiter);
    }
    else
    {
        if (($strRegion === 'Bourgogne') &&
            ($strJpnRating === 'グラン・クリュ') &&
            ($strVillage !== 'シャブリ'))
        {
            $wineName = appendToken($wineName, $strName, $delimiter);
            $wineName = appendToken($wineName, $strJpnRating, $delimiter);
        }
        else
        {
            $wineName = appendToken($wineName, $strVillage, $delimiter);
            $wineName = appendToken($wineName, $strJpnRating, $delimiter);
            $wineName = appendToken($wineName, $strName, $delimiter);
        }
    }

    return $wineName;
}

function importWineCsv($filePath)
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

    dropTable($tableName);

    $mysqli  = connectToMySql();

    for ($i = 1; $i < $cLine; ++$i)
    {
        $j = 0;
        $rgobjCol = array();

        $rgstrColValue = explode($separator, $rgstrRow[$i]);

        // Trim extra double quotes from the first and last column value.
        $rgstrColValue[0] = ltrim($rgstrColValue[0], "\"");
        $rgstrColValue[count($rgstrColValue) - 1] = rtrim($rgstrColValue[count($rgstrColValue) - 1], "\"");

        $rgintCapacity = array();

        $strPoint = '';
        foreach ($rgstrColName as $strColName)
        {
            if ($strColName !== 'wholesale_price')
            {
                $colValue = $rgstrColValue[$j++];
                $colValue = str_replace("\"\"", "\"", $colValue);

                if ($strColName === 'point')
                {
                    $colValue = replaceWineScoreAbbreviation(trim($colValue));
                }

                $rgobjCol[$strColName] = $colValue; 
            }
        }

        if ($rgobjCol['stock'] < 0)
        {
            $rgobjCol['stock'] = 0;
        }

        $rgobjCol['combined_name'] = generateWineName(
            $rgobjCol['type'],
            $rgobjCol['country'],
            $rgobjCol['region'],
            $rgobjCol['village'],
            $rgobjCol['rating'],
            $rgobjCol['name'],
            $rgobjCol['producer']);

        $rgobjCol['combined_name_jpn'] = generateJpnWineName(
            $rgobjCol['type'],
            $rgobjCol['country'],
            $rgobjCol['region'],
            $rgobjCol['village_jpn'],
            $rgobjCol['rating'],
            $rgobjCol['rating_jpn'],
            $rgobjCol['name_jpn'],
            $rgobjCol['producer_jpn']);

        $rgobjCol['capacity'] =
            max($rgobjCol['capacity1'], $rgobjCol['capacity2'], $rgobjCol['capacity3'], $rgobjCol['capacity4']);

        unset($rgobjCol['capacity1']);
        unset($rgobjCol['capacity2']);
        unset($rgobjCol['capacity3']);
        unset($rgobjCol['capacity4']);

        $rgstrQuery[] = generateSecureUpdateTableQuery($mysqli, $tableName, $rgobjCol, 0);
    }

    // Pops 'wholesale_price' column.
    array_pop($rgstrColName);

    // Pops 'capacity1', 'capacity2', 'capacity3', 'capacity4' columns.
    array_pop($rgstrColName);
    array_pop($rgstrColName);
    array_pop($rgstrColName);
    array_pop($rgstrColName);

    // Add a combined column, which contains the wine name.
    array_push($rgstrColName, 'combined_name', 'combined_name_jpn', 'capacity');

    createTable($tableName, $rgstrColName, 'id');

    echo "Finished reading data from the CSV file.";

    $fFailed = FALSE;
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

function checkoutWine($dbc, $barcode, $qty)
{
    $setId = $barcode - 50000;
    if ($setId > 0)
    {
        mysqli_query($dbc, "CALL checkout_wine_set('$setId', '$qty', @fSuccess)");
    }
    else
    {
        mysqli_query($dbc, "CALL checkout_wine('$barcode', '$qty', @fSuccess)");
    }

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
        echo "<br />&nbsp;&nbsp;&nbsp;&nbsp;$barcode [x$qty]";
    }
    else
    {
        echo "<br />Failed to Checked out $barcode [x$qty]";
        echo "<br />... Temporarily setting stock to 0 in order to prevent double-booking.";

        prepareNextQuery($dbc);
        mysqli_query($dbc, "CALL checkout_all_wines('$barcode')");
    }
}

function getDeliveredWines($dbc)
{
    date_default_timezone_set('UTC');
    $year  = date('Y');
    $month = date('m');
    $date  = date('d');

    prepareNextQuery($dbc);
    return mysqli_query($dbc, "CALL get_delivered_wines('$year', '$month', '$date')");
}

formatCsv($csvFilePath);

if (importWineCsv($csvFilePath))
{
    echo('<br /><br />Synced :)');

    $targetScript  = './wholesale/sync.php';
    include_once("$curDirPath/execute_in_background.php");

    $orderId = '00000000-0000000000';
    $result  = mysqli_query($dbc, "CALL get_order_contents('$orderId')");
    if ($result !== FALSE)
    {
        $strStatus = '';

        echo('<br /><br />Checking-out reserved wines...<br />');

        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
        {
            $strStatus = $row['status'];
            if ((($strStatus === '0') || ($strStatus === '1') || ($strStatus === '2')) && !empty($row['contents']))
            {
                $rgstrItem = explode(';', $row['contents']);
                $cItem = count($rgstrItem);
                for ($i = 0; $i < $cItem; ++$i)
                {
                    $rgstrToken = explode('#', $rgstrItem[$i]);
                    if (count($rgstrToken) === 2)
                    {
                        $barcode = $rgstrToken[0];
                        $qty     = $rgstrToken[1];

                        prepareNextQuery($dbc);
                        checkoutWine($dbc, $barcode, $qty);
                    }
                }
            }
        }

        mysqli_free_result($result);
    }

    $result = getDeliveredWines($dbc);
    if ($result !== FALSE)
    {
        echo('<br /><br />Checking-out delivered wines...<br />');
        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
        {
            prepareNextQuery($dbc);
            checkoutWine($dbc, $row['barcode_number'], '1');
        }

        mysqli_free_result($result);
    }
}
else
{
    echo('<br /><br />Failed to sync :(');
}
 
mysqli_close($dbc);
unlink($csvFilePath);
file_put_contents($dbSyncStatusFilePath, '0');

?>
