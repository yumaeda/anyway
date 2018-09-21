<?php

$curDirPath = dirname(__FILE__);
require_once("$curDirPath/../dbutils.php");

dropTable('preorder_wines');

echo '<script type="text/javascript">window.location.replace(\'./index.php\');</script>';

?>
