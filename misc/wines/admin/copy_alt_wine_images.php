<?php

$curDirPath      = dirname(__FILE__);
$definesFilePath = "$curDirPath/defines.php";
require_once("$curDirPath/../../restaurant/common.php");

class Wine
{
    public $id;
    public $name;
    public $producer;
    public $type;
}

function prepareNextQuery($dbc)
{
    while ($dbc->more_results())
    {
        $dbc->next_result();
        if ($res = $dbc->store_result())
        {
            $res->free(); 
        }
    }
}

$rgobjWine = array();

// Executing SQL query directly since calling through stored procedure takes forever.
$strQuery = "SELECT * FROM wines WHERE (availability = 'Online') AND (apply <> 'DP');";
$result   = mysqli_query($dbc, $strQuery);
if ($result !== FALSE)
{
    while ($row = mysqli_fetch_assoc($result))
    {
        $producer = $row['producer'];
        $type     = $row['type'];
        $name     = $row['combined_name'];

        $fileName = ($row['barcode_number'] . '.png');
        $imgUri   = "../../images/wines/400px/$fileName";
        if (file_exists($imgUri))
        {

            prepareNextQuery($dbc);
            mysqli_query($dbc, "CALL add_wine_image('$producer', '$type', '$name', '$fileName')");
        }
        else
        {
            $objTmpWine = new Wine();
            $objTmpWine->id       = $row['barcode_number'];
            $objTmpWine->producer = $producer;
            $objTmpWine->type     = $type;
            $objTmpWine->name     = $name;

            $rgobjWine[] = $objTmpWine;
        }
    }

    mysqli_free_result($result);

    for ($i = 0; $i < count($rgobjWine); ++$i)
    {
        $objWine = $rgobjWine[$i];

        $targetFileName = $objWine->id . '.png';
        $producer       = $objWine->producer;
        $type           = $objWine->type;
        $name           = $objWine->name;

        prepareNextQuery($dbc);
        $imgResult = mysqli_query($dbc, "CALL get_wine_image('$producer', '$type', '$name')");
        if ($imgResult !== FALSE)
        {
            $imgRow = mysqli_fetch_assoc($imgResult);
            $srcFileName = $imgRow['file_name'];
            if (!empty($srcFileName))
            {
                $srcFilePath    = "../../images/wines/400px/$srcFileName";
                $targetFilePath = "../../images/wines/400px/$targetFileName";
                    
                if (copy($srcFilePath, $targetFilePath))
                {
                    $srcFilePath    = "../../images/wines/200px/$srcFileName";
                    $targetFilePath = "../../images/wines/200px/$targetFileName";
                    if (copy($srcFilePath, $targetFilePath))
                    {
                        $srcFilePath    = "../../images/wines/100px/$srcFileName";
                        $targetFilePath = "../../images/wines/100px/$targetFileName";
                        if (copy($srcFilePath, $targetFilePath))
                        {
                            echo "Copied $srcFileName to  $targetFileName !!<br />";
                        }
                    }
                }
            }

            mysqli_free_result($imgResult);
        }
    }
}

mysqli_close($dbc);

?>
