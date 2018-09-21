<?php

$curDirPath = dirname(__FILE__);
file_put_contents("$curDirPath/../../syncStatus.txt", '0');

echo '
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
        <meta http-equiv="refresh" content="0; url=http://anyway-grapes.jp/wines/admin/upload.php" />
    </head>
    <body>
    </body>
</html>
';

?>
