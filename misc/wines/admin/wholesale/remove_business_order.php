<?php

$curDirPath = dirname(__FILE__);
require_once("$curDirPath/defines.php");
require_once("$curDirPath/../../../includes/config.inc.php");
require_once(MYSQL);

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    if (isset($_POST['id']))
    {
        $orderId = $_POST['id'];
        $result  = mysqli_query($dbc, "CALL wholesale.remove_business_order($orderId)");
        if (($result !== FALSE) && (mysqli_affected_rows($dbc) == 1))
        {
            echo 'SUCCESS';
        }
        else
        {
            echo 'FAIL';
        }
    }
}

mysqli_close($dbc);

?>
