<?php

$curDirPath      = dirname(__FILE__);
$definesFilePath = "$curDirPath/defines.php";
require_once("$curDirPath/../../../restaurant/common.php");

if ($_SERVER['REQUEST_METHOD'] === 'GET')
{
    $result  = mysqli_query($dbc, "CALL wholesale.get_business_customer_names()");
    if ($result !== FALSE)
    {
        echo convertResultToJson($result);
        mysqli_free_result($result);
    }
}

mysqli_close($dbc);

?>
